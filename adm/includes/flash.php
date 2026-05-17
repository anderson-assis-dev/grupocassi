<?php
function setFlash(string $type, string $msg): void
{
    $_SESSION['_flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array
{
    $f = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return $f;
}
