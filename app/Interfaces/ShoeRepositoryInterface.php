<?php

namespace App\Interfaces;

interface ShoeRepositoryInterface
{
    public function index();
    public function show(string $slug);
}