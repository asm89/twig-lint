<?php

namespace Asm89\Twig\Lint\Tokenizer;

class Tokenizer implements TokenizerInterface
{
    const STATE_DATA        = 0;
    const STATE_BLOCK       = 1;
    const STATE_VAR         = 2;
    const STATE_COMMENT     = 3;

    const REGEX_NAME = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/A';
    const REGEX_NUMBER = '/[0-9]+(?:\.[0-9]+)?/A';
    const REGEX_STRING = '/"([^#"\\\\]*(?:\\\\.[^#"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/As';
    const REGEX_DQ_STRING_DELIM = '/"/A';
    const REGEX_DQ_STRING_PART = '/[^#"\\\\]*(?:(?:\\\\.|#(?!\{))[^#"\\\\]*)*/As';
    const PUNCTUATION = '()[]{}?:.,|';

    protected $env;

    protected $options;

    protected $regexes;

    public function __construct(\Twig_Environment $env, array $options = array())
    {
        $this->env = $env;

        $this->regexes = [];

        $this->options = array_merge(array(
            'tag_comment' => array('{#', '#}'),
            'tag_block' => array('{%', '%}'),
            'tag_variable' => array('{{', '}}'),
            'whitespace_trim' => '-',
            'interpolation' => array('#{', '}'),
        ), $options);

        $this->regexes['tokens_start'] = '/('.preg_quote($this->options['tag_variable'][0], '/').'|'.preg_quote($this->options['tag_block'][0], '/').'|'.preg_quote($this->options['tag_comment'][0], '/').')('.preg_quote($this->options['whitespace_trim'], '/').')?/s';
        $this->regexes['lex_block'] = '/('.preg_quote($this->options['whitespace_trim']).')?('.preg_quote($this->options['tag_block'][1]).')/';
        $this->regexes['lex_variable'] = '/('.preg_quote($this->options['whitespace_trim']).')?('.preg_quote($this->options['tag_variable'][1]).')/';
        $this->regexes['lex_comment'] = '/('.preg_quote($this->options['whitespace_trim']).')?('.preg_quote($this->options['tag_comment'][1]).')/';
        $this->regexes['operator'] = $this->getOperatorRegex();
    }

    protected function resetState()
    {
        $this->cursor = 0;
        $this->lineno = 1;
        $this->currentPosition = 0;
        $this->tokens = [];
    }

    protected function preflightSource($code)
    {
        $tokenPositions = [];
        preg_match_all($this->regexes['tokens_start'], $code, $tokenPositions, PREG_OFFSET_CAPTURE);

        $tokenPositionsReworked = [];
        foreach ($tokenPositions[0] as $index => $tokenFullMatch) {
            $tokenPositionsReworked[$index] = [
                'fullMatch' => $tokenFullMatch[0],
                'position' => $tokenFullMatch[1],
                'match' => $tokenPositions[1][$index][0],
            ];
        }

        return $tokenPositionsReworked;
    }

    protected function moveCurrentPosition($value = 1)
    {
        $this->currentPosition += $value;
    }

    protected function moveCursor($value)
    {
        $this->cursor += strlen($value);
        $this->lineno += substr_count($value, "\n");
    }

    protected function getTokenPosition($tokenPosition = null)
    {
        if (null === $tokenPosition) {
            $tokenPosition = $this->currentPosition;
        }

        if (empty($this->tokenPositions)) {
            // No token at all found during preflight.
            return null;
        }

        if (!isset($this->tokenPositions[$this->currentPosition])) {
            // No token for current position.
            return null;
        }

        return $this->tokenPositions[$this->currentPosition];
    }

    protected function pushToken($type, $value = null)
    {
        $tokenPositionInLine = $this->cursor - strrpos(substr($this->code, 0, $this->cursor), PHP_EOL);

        $this->tokens[] = new Token($type, $this->lineno, $tokenPositionInLine, $this->filename, $value);
    }

    protected function getState()
    {
        return !empty($this->state) ? $this->state[count($this->state) - 1]: self::STATE_DATA;
    }

    protected function pushState($state)
    {
        $this->state[] = $state;
    }

    protected function popState()
    {
        if (0 === count($this->state)) {
            throw new \Exception('Cannot pop state without a previous state');
        }

        array_pop($this->state);
    }

    public function tokenize($code, $filename = null)
    {
        // Reset everything.
        $this->resetState();

        $this->code = $code;
        $this->end = strlen($code);
        $this->filename = $filename;

        // Preflight source code for token positions.
        $this->tokenPositions = $this->preflightSource($code);
        while ($this->cursor < $this->end) {
            $nextToken = $this->getTokenPosition();

            switch ($this->getState()) {
                case self::STATE_BLOCK:
                    $this->lexBlock();

                    break;
                case self::STATE_VAR:
                    $this->lexVariable();

                    break;
                case self::STATE_COMMENT:
                    $this->lexComment();

                    break;
                case self::STATE_DATA:
                default:
                    if ($this->cursor === $nextToken['position']) {
                        $this->lexStart();
                    } else {
                        $this->lexData();
                    }
                    break;
            }
        }

        if (self::STATE_DATA !== $this->getState()) {
            throw new \Exception("Error Processing Request", 1);
        }

        $this->pushToken(Token::EOF_TYPE);

        return $this->tokens;
    }

    protected function lex($endType, $end, $endRegex)
    {
        preg_match($endRegex, $this->code, $match, PREG_OFFSET_CAPTURE, $this->cursor);
        if ($match[0][1] === $this->cursor) {
            $this->pushToken($endType, $match[0][0]);
            $this->moveCursor($match[0][0]);
            $this->moveCurrentPosition();
            $this->popState();
        } else {
            if ($this->getState() === self::STATE_COMMENT) {
                // Parse as text until the end position.
                $this->lexData($match[0][1]);
            } else {
                while ($this->cursor < $match[0][1]) {
                    $this->lexExpression();
                }
            }
        }
    }

    protected function lexExpression()
    {
        $currentToken = $this->code[$this->cursor];
        if (' ' === $currentToken) {
            $this->lexWhitespace();
        } elseif (PHP_EOL === $currentToken) {
            $this->lexEOL();
        } elseif (preg_match($this->regexes['operator'], $this->code, $match, null, $this->cursor)) {
            // operators
            $this->pushToken(Token::OPERATOR_TYPE, $match[0]);
            $this->moveCursor($match[0]);
        } elseif (preg_match(self::REGEX_NAME, $this->code, $match, null, $this->cursor)) {
            // names
            $this->pushToken(Token::NAME_TYPE, $match[0]);
            $this->moveCursor($match[0]);
        } elseif (preg_match(self::REGEX_NUMBER, $this->code, $match, null, $this->cursor)) {
            // numbers
            $number = (float) $match[0];  // floats
            if (ctype_digit($match[0]) && $number <= PHP_INT_MAX) {
                $number = (int) $match[0]; // integers lower than the maximum
            }
            $this->pushToken(Token::NUMBER_TYPE, $number);
            $this->moveCursor($match[0]);
        } elseif (false !== strpos(self::PUNCTUATION, $this->code[$this->cursor])) {
            // punctuation

            // opening bracket
            if (false !== strpos('([{', $this->code[$this->cursor])) {
                $this->brackets[] = array($this->code[$this->cursor], $this->lineno);
            }
            // closing bracket
            elseif (false !== strpos(')]}', $this->code[$this->cursor])) {
                if (empty($this->brackets)) {
                    throw new \Exception(sprintf('Unexpected "%s".', $this->code[$this->cursor]));
                }

                list($expect, $lineno) = array_pop($this->brackets);
                if ($this->code[$this->cursor] != strtr($expect, '([{', ')]}')) {
                    throw new \Exception(sprintf('Unclosed "%s".', $expect));
                }
            }

            $this->pushToken(Token::PUNCTUATION_TYPE, $this->code[$this->cursor]);
            $this->moveCursor($this->code[$this->cursor]);
        } elseif (preg_match(self::REGEX_STRING, $this->code, $match, null, $this->cursor)) {
            // strings
            $this->pushToken(Token::STRING_TYPE, stripcslashes($match[0]));
            $this->moveCursor($match[0]);
        } else {
            // unlexable
            throw new \Exception(sprintf('Unexpected character "%s".', $this->code[$this->cursor]));
        }
    }

    protected function lexBlock()
    {
        $this->lex(Token::BLOCK_END_TYPE, $this->options['tag_block'][1], $this->regexes['lex_block']);
    }

    protected function lexVariable()
    {
        $this->lex(Token::VAR_END_TYPE, $this->options['tag_variable'][1], $this->regexes['lex_variable']);
    }

    protected function lexComment()
    {
        $this->lex(Token::COMMENT_END_TYPE, $this->options['tag_comment'][1], $this->regexes['lex_comment']);
    }

    protected function lexData($limit = null)
    {
        $nextToken = $this->getTokenPosition();
        if (null === $limit) {
            $limit = $nextToken['position'];
        }

        $currentToken = $this->code[$this->cursor];
        if (preg_match('/\t/', $currentToken)) {
            $this->lexTab();
        } elseif (' ' === $currentToken) {
            $this->lexWhitespace();
        } elseif (PHP_EOL === $currentToken) {
            $this->lexEOL();
        } elseif (preg_match('/\S+/', $this->code, $match, null, $this->cursor)) {
            $value = $match[0];

            // Stop if cursor reaches the next token start.
            if ($limit <= ($this->cursor + strlen($value))) {
                $value = substr($value, 0, $nextToken['position'] - $this->cursor);
            }

            // Fixing token start among expressions and comments.
            $nbTokenStart = preg_match_all($this->regexes['tokens_start'], $value);
            if ($nbTokenStart) {
                $this->moveCurrentPosition($nbTokenStart);
            }

            $this->pushToken(Token::TEXT_TYPE, $value);
            $this->moveCursor($value);
        }
    }

    protected function lexStart()
    {
        $tokenStart = $this->getTokenPosition();
        if ($tokenStart['match'] === $this->options['tag_comment'][0]) {
            $state = self::STATE_COMMENT;
            $tokenType = Token::COMMENT_START_TYPE;
        } elseif ($tokenStart['match'] === $this->options['tag_block'][0]) {
            $state = self::STATE_BLOCK;
            $tokenType = Token::BLOCK_START_TYPE;
        } elseif ($tokenStart['match'] === $this->options['tag_variable'][0]) {
            $state = self::STATE_VAR;
            $tokenType = Token::VAR_START_TYPE;
        }

        $this->pushToken($tokenType, $tokenStart['fullMatch']);
        $this->pushState($state);
        $this->moveCursor($tokenStart['fullMatch']);
    }

    protected function lexTab()
    {
        $this->pushToken(Token::TAB_TYPE);
        $this->moveCursor($this->code[$this->cursor]);
    }

    protected function lexWhitespace()
    {
        $this->pushToken(Token::WHITESPACE_TYPE, $this->code[$this->cursor]);
        $this->moveCursor($this->code[$this->cursor]);
    }

    protected function lexEOL()
    {
        $this->pushToken(Token::EOL_TYPE, $this->code[$this->cursor]);
        $this->moveCursor($this->code[$this->cursor]);
    }

    protected function getOperatorRegex()
    {
        $operators = array_merge(
            array('='),
            array_keys($this->env->getUnaryOperators()),
            array_keys($this->env->getBinaryOperators())
        );

        $operators = array_combine($operators, array_map('strlen', $operators));
        arsort($operators);

        $regex = array();
        foreach ($operators as $operator => $length) {
            // an operator that ends with a character must be followed by
            // a whitespace or a parenthesis
            if (ctype_alpha($operator[$length - 1])) {
                $r = preg_quote($operator, '/').'(?=[\s()])';
            } else {
                $r = preg_quote($operator, '/');
            }

            // an operator with a space can be any amount of whitespaces
            $r = preg_replace('/\s+/', '\s+', $r);

            $regex[] = $r;
        }

        return '/'.implode('|', $regex).'/A';
    }
}
