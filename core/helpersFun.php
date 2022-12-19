<?php


function dd($value, bool $error = false){
    $color = $error ? "#fb9898;" : "#1ebef7";

    echo "<p></p>";
    echo "<div style='background-color:#323232;color:".$color."'><pre>";
    print_r("<br><br>");
    if ($error){
        print_r(json_encode(["Error" => $value]));
    }else{
        print_r($value);
    }
    print_r("<br><br><br>");
    echo "</pre></div>";
    die();
}

function array_get($array,$key){
    return $array[$key] ?? null;
}

function _e($value): string
{
    return htmlspecialchars($value);
}
