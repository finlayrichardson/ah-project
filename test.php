<?php
function test($query, ...$vars) {
    echo var_dump($vars);
}

test("hi", 1, "email", true);
