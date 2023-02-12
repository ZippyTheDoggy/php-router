<?php

function parse($code, $variables) {
    $idx = -1;
    $tokens = [];
    $buffer = "";

    $IS_NUMERIC = function ($char) {
        return preg_match("/[0-9]/", $char) == 1;
    };
    $IS_DECIMAL = function($char) {
        return $char == ".";
    };
    $IS_QUOTE = function ($char) {
        return ($char == '"') || ($char == "'");
    };
    $IS_TEXT = function ($char) {
        return preg_match("/[^'\"]/", $char) == 1;
    };

    function spos($str, $pos) {
        return substr($str, $pos, $pos);
    }

    $col = 0;
    $line = -1;
    while($idx < strlen($code)) {
        $idx++;
        $col++;
        if(spos($code, $idx) == "\n") {
            $col = 0;
            $line++;
        }
        if (spos($code, $idx) == " ")
            continue;
        if($IS_NUMERIC(spos($code, $idx)) || $IS_DECIMAL(spos($code, $idx))) {
            while($IS_NUMERIC(spos($code, $idx)) || ($IS_DECIMAL(spos($code, $idx)) && !str_contains($buffer, "."))) {
                $buffer .= spos($code, $idx);
                $idx++;
            }
            if($IS_DECIMAL(spos($code, $idx))) {
                array_push($errors, "Number has two decimals at $line:$col");
            } else {
                $idx -=2;
            }
            $tokens[] = [
                "type" => "number",
                "value" => $buffer
            ];
        } elseif($IS_QUOTE(spos($code, $idx))) {
            while($IS_TEXT(spos($code, $idx))) {
                $buffer .= spos($code, $idx);
                $idx++;
            }
            if($idx >= strlen($code)) {
                array_push($errors, "Unterminated string at $line:$col");
            } else {
                $idx -= 2;
            }
            $tokens[] = [
                "type" => "string",
                "value" => $buffer
            ];
        }
    }
    echo count($tokens);
}

echo parse("1 + var", [ "var" => 5 ]);

?>