<?php

namespace Frame\Session;

interface SessionDriverInterface
{
    public function start(): bool;
    public function regenerate(): bool;
    public function destroy(): bool;
    public function get(string $key);
    public function set(string $key, $value): void;
    public function has(string $key): bool;
    public function all(): array;
    public function remove(string $key): void;
    public function clear(): void;
    public function getId(): string;
}