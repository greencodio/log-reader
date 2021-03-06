<?php

namespace Stevebauman\LogReader\Objects;

use Illuminate\Support\Facades\Cache;

class Entry
{
    /**
     * The entry's ID.
     *
     * @var string
     */
    public $id = '';

    /**
     * The entry's file path.
     *
     * @var string
     */
    public $filePath = '';

    /**
     * The entry's level string.
     *
     * @var string
     */
    public $level = '';

    /**
     * The entry's header string.
     *
     * @var string
     */
    public $header = '';

    /**
     * The entry's date string.
     *
     * @var string
     */
    public $date = '';

    /**
     * The entry's stack string.
     *
     * @var string
     */
    public $stack = '';

    /**
     * The entry's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Constructs a new entry object with the specified attributes.
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->setAttributes($attributes);

        $this->assignAttributes();
    }

    /**
     * Stores the entry in the cache so it is no longer shown
     * in the log results.
     *
     * @return mixed
     */
    public function markRead()
    {
        return Cache::rememberForever($this->makeCacheKey(), function () {
            return $this;
        });
    }

    /**
     * Returns true/false depending if the entry
     * has been marked read (exists inside the cache).
     *
     * @return bool
     */
    public function isRead()
    {
        if (Cache::has($this->makeCacheKey())) {
            return true;
        }

        return false;
    }

    /**
     * Removes the current entry from the log file.
     *
     * @return bool
     */
    public function delete()
    {
        $filePath = $this->getFilePath();

        $contents = file_get_contents($filePath);

        $contents = str_replace($this->header.$this->stack, '', $contents);

        file_put_contents($filePath, $contents);

        return true;
    }

    /**
     * Returns the current entry's file path.
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Retrieves an attribute by the specified key
     * from the attributes array.
     *
     * @param int|string $key
     *
     * @return int|string|null
     */
    public function getAttribute($key)
    {
        $attributes = $this->getAttributes();

        if (array_key_exists($key, $attributes)) {
            return $attributes[$key];
        }

        return;
    }

    /**
     * Returns a attributes array.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns a compressed entry header suitable to
     * be used as the entry's ID.
     *
     * @return string
     */
    private function makeId()
    {
        return md5($this->header);
    }

    /**
     * Returns a key string for storing the entry
     * inside the cache.
     *
     * @return string
     */
    private function makeCacheKey()
    {
        return $this->id;
    }

    /**
     * Sets the entry's filePath property.
     *
     * @param $path
     */
    private function setFilePath($path = null)
    {
        if ($path) {
            $this->filePath = $path;
        }
    }

    /**
     * Sets the entry's level property.
     *
     * @param $header
     */
    private function setHeader($header = null)
    {
        if ($header) {
            $this->header = $header;
        }
    }

    /**
     * Sets the entry's date property by the
     * inserted header.
     *
     * @param string $header
     */
    private function setDateByHeader($header = '')
    {
        preg_match_all("/\[([^\]]*)\]/", $header, $matches);

        if (is_array($matches) && array_key_exists(1, $matches)) {
            $this->date = $matches[1][0];
        }
    }

    /**
     * Sets the entry's level property.
     *
     * @param $stack
     */
    private function setStack($stack = null)
    {
        if ($stack) {
            $this->stack = $stack;
        }
    }

    /**
     * Sets the entry's level property.
     *
     * @param $level
     */
    private function setLevel($level = null)
    {
        if ($level) {
            $this->level = $level;
        }
    }

    /**
     * Sets the entry's ID property.
     *
     * @param $id
     */
    private function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Sets the attributes property.
     *
     * @param array $attributes
     */
    private function setAttributes($attributes = [])
    {
        if (is_array($attributes)) {
            $this->attributes = $attributes;
        }
    }

    /**
     * Assigns the valid keys in the attributes array
     * to the properties in the entry.
     */
    private function assignAttributes()
    {
        $this->setFilePath($this->getAttribute('filePath'));
        $this->setLevel($this->getAttribute('level'));
        $this->setHeader($this->getAttribute('header'));
        $this->setStack($this->getAttribute('stack'));
        $this->setDateByHeader($this->getAttribute('header'));
        $this->setId($this->makeId());
    }
}
