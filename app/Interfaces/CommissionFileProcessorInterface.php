<?php

namespace App\Interfaces;

interface CommissionFileProcessorInterface
{
    public function fileDataToArray(string $file_path): array;
}