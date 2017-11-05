<?php

namespace Asm89\Twig\Lint\Preprocessor;

class Preprocessor
{
    const STATE_DATA        = 0;
    const STATE_BLOCK       = 1;
    const STATE_VAR         = 2;
    const STATE_COMMENT     = 3;

    const STATE_NADA        = 69;
    const STATE_STOP        = 70;
    const STATE_TOKEN_START = 71;
    const STATE_TOKEN_STOP  = 72;

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
        // dump($this->regexes);
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
        // dump(['currentPosition' => $this->currentPosition]);
        $this->currentPosition += $value;
    }

    protected function moveCursor($value = 1)
    {
        $this->cursor += $value;
        // $this->lineno += substr_count($value, "\n");
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

    protected function pushToken($type, $lineno, $cusorPosition, $value = null)
    {
        $this->lineno = $this->lineno + substr_count($value, "\n");
        $this->tokens[] = [$type, $value, $this->lineno, $cusorPosition];
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
        // $tokenPositions = [];
        // preg_match_all($this->regexes['tokens_start'], $code, $tokenPositions, PREG_OFFSET_CAPTURE);

        // foreach ($this->preflightSource($code) as $position) {
        //     $cursorPosition = $position[1];

        //     $look = $cursorPosition + strlen($this->options['tag_variable'][0]);
        //     if ($this->options['whitespace_trim'] === $code[$look]) {
        //         $look = $look + 1;
        //     }

        //     // dump(substr($code, 0, $cursorPosition));
        //     $lineno = 1 + substr_count(substr($code, 0, $cursorPosition), "\n");
        //     $match = '';
        //     if (' ' !== $code[$look]) {
        //         // dump(substr($code, $cursorPosition, $look - $cursorPosition));
        //         dump('Need whitespace! Found "' . str_replace("\n", "\\n", substr($code, $cursorPosition, $look - $cursorPosition + 10)) . '" at line "' . $lineno . '"');
        //     }

        //     if (' ' === $code[$look] && preg_match('/\s+/', $code, $match, null, $look)) {
        //         dump('Only one whitespace needed! Found "' .  substr($code, $cursorPosition, $look - $cursorPosition + 5) . '" at line "' . $lineno . "'");
        //     }
        // }

        $this->code = $code;
        $this->end = strlen($code);
        $this->cursor = 0;
        $this->lineno = 1;
        $this->currentPosition = 0;
        $this->tokens = [];
        $this->tokenPositions = $this->preflightSource($code);

        // dump($this->tokenPositions);

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
                    // dump([$this->cursor]);
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

        $this->pushToken(Token::EOF_TYPE, 0, 0);


        dump($this->tokens);
        exit;
    }

    protected function lex($endType, $end, $endRegex)
    {

        // dump($this->cursor, $match[0][1] - $this->cursor);

        // while ($this->cursor < $this->end) {
        preg_match($endRegex, $this->code, $match, PREG_OFFSET_CAPTURE, $this->cursor);
        // dump($this->cursor);
        if ($match[0][1] === $this->cursor) {
            $this->pushToken($endType, 0, 0, $match[0][0]);
            $this->moveCursor(strlen($match[0][0]));
            $this->moveCurrentPosition();
            $this->popState();
        } else {
            $this->pushToken(-2, 0, 0, substr($this->code, $this->cursor, $match[0][1] - $this->cursor));
            $this->moveCursor($match[0][1] - $this->cursor);
        }
        // }
        // exit;
    }

    protected function lexExpression()
    {
        // $this->moveCursor();
    }

    protected function lexBlock()
    {
        dump('block');

        $this->lex(Token::BLOCK_END_TYPE, $this->options['tag_block'][1], $this->regexes['lex_block']);
    }

    protected function lexVariable()
    {
        dump('variable');

        $this->lex(Token::VAR_END_TYPE, $this->options['tag_variable'][1], $this->regexes['lex_variable']);
    }

    protected function lexComment()
    {
        dump('comment');

        $this->lex(Token::COMMENT_END_TYPE, $this->options['tag_comment'][1], $this->regexes['lex_comment']);
    }

    protected function lexData()
    {
        dump('data');

        // while ($this->cursor < $this->end) {
            $this->lexRegular();
        // }
    }

    protected function lexRegular()
    {
        // while ($this->cursor < $this->end) {
            $currentToken = $this->code[$this->cursor];
            // dump($currentToken);
            $nextToken = $this->getTokenPosition();
            // dump($nextToken);
            if (' ' === $currentToken) {
                $this->lexWhitespace();
            } elseif (PHP_EOL === $currentToken) {
                $this->lexEOL();
            } elseif (preg_match('/\S+/', $this->code, $match, null, $this->cursor)) {
                $value = $match[0];
                dump(['match' => $match, 'cursor' => $this->cursor, 'limit' => $nextToken['position']]);
                if ($nextToken['position'] <= ($this->cursor + strlen($match[0]))) {
                    $value = substr($value, 0, $nextToken['position'] - $this->cursor);
                }

                $this->pushToken(Token::TEXT_TYPE, 0, 0, $value);
                $this->moveCursor(strlen($value));
            }
        // }
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

        $this->pushToken($tokenType, 0, 0, $tokenStart['fullMatch']);
        $this->pushState($state);
        $this->moveCursor(strlen($tokenStart['fullMatch']));
    }

    protected function lexWhitespace()
    {
        $this->pushToken(Token::WHITESPACE_TYPE, 0, 0, $this->code[$this->cursor]);
        $this->moveCursor();
    }

    protected function lexEOL()
    {
        $this->pushToken(Token::EOL_TYPE, 0, 0, $this->code[$this->cursor]);
        $this->moveCursor();
    }
}
