<?php

class Resource {
    protected $id;
    protected $title;
    protected $genres;
    protected $themes;
    protected $poster;
    protected $dateAdded;

    public function __construct($id, $title, $genres = [], $themes = [], $poster = null, $dateAdded = null) {
        $this->id = $id;
        $this->title = $title;
        $this->genres = is_array($genres) ? $genres : [];
        $this->themes = is_array($themes) ? $themes : [];
        $this->poster = $poster;
        $this->dateAdded = $dateAdded;
    }

    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getGenres() { return $this->genres; }
    public function getThemes() { return $this->themes; }
    public function getPoster() { return $this->poster; }
    public function getDateAdded() { return $this->dateAdded; }
}
