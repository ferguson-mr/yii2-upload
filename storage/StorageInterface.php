<?php

namespace ferguson\upload\storage;

interface StorageInterface
{
    public function save();

    public function resize();

    public function delete($file);
}