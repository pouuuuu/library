<?php

require_once __DIR__ . '/Resource.php';

class Book extends Resource {
    private $isbn;
    private $editor;
    private $publishYear;
    private $price;
    private $nbPages;
    private $edition;
    private $type;
    private $language;
    private $authors;
    private $description;

    public function __construct(
        $id,
        $title,
        $genres = [],
        $themes = [],
        $poster = null,
        $dateAdded = null,
        $description = null,
        $isbn = null,
        $editor = null,
        $publishYear = null,
        $price = null,
        $nbPages = null,
        $edition = null,
        $type = null,
        $language = null,
        $authors = []
    ) {
        parent::__construct($id, $title, $genres, $themes, $poster, $dateAdded);
        $this->isbn = $isbn;
        $this->editor = $editor;
        $this->description = $description;
        $this->publishYear = $publishYear;
        $this->price = $price;
        $this->nbPages = $nbPages;
        $this->edition = $edition;
        $this->type = $type;
        $this->language = $language;
        $this->authors = is_array($authors) ? $authors : [];
    }

    public function getIsbn() { return $this->isbn; }
    public function getEditor() { return $this->editor; }
    public function getPublishYear() { return $this->publishYear; }
    public function getPrice() { return $this->price; }
    public function getNbPages() { return $this->nbPages; }
    public function getEdition() { return $this->edition; }
    public function getType() { return $this->type; }
    public function getLanguage() { return $this->language; }
    public function getAuthors() { return $this->authors; }
    public function getDescription() { return $this->description; }

	public function toArray() {
    return [
        'id' => $this->id,
        'type' => 'book',
        'title' => $this->title,
        'poster' => $this->poster,
        'authors' => $this->auteurs ?? [],
        'publishYear' => $this->publishYear,
        'language' => $this->langue ?? null,
    ];
}

}
