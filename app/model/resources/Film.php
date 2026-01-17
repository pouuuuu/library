<?php

require_once __DIR__ . '/Resource.php';

class Film extends Resource {
    private $synopsis;
    private $productionYear;
    private $releaseDate;
    private $trailer;
    private $duration;
    private $type;
    private $languages;
    private $productionCountries;
    private $plotCountries;
    private $proposedBy;

    public function __construct($id, $title, $genres, $themes, $poster, $dateAdded, $synopsis, $productionYear, $releaseDate, $trailer, $duration, $type, $languages, $productionCountries, $plotCountries, $proposedBy) {
        parent::__construct($id, $title, $genres, $themes, $poster, $dateAdded);
        $this->synopsis = $synopsis;
        $this->productionYear = $productionYear;
        $this->releaseDate = $releaseDate;
        $this->trailer = $trailer;
        $this->duration = $duration;
        $this->type = $type;
        $this->languages = $languages;
        $this->productionCountries = $productionCountries;
        $this->plotCountries = $plotCountries;
        $this->proposedBy = $proposedBy;
    }

    public function getSynopsis() { return $this->synopsis; }
    public function getProductionYear() { return $this->productionYear; }
    public function getReleaseDate() { return $this->releaseDate; }
    public function getTrailer() { return $this->trailer; }
    public function getDuration() { return $this->duration; }
    public function getType() { return $this->type; }
    public function getLanguages() { return $this->languages; }
    public function getProductionCountries() { return $this->productionCountries; }
    public function getPlotCountries() { return $this->plotCountries; }
    public function getProposedBy() { return $this->proposedBy; }

	public function toArray() {
    return [
        'id' => $this->id,
        'type' => 'film',
        'title' => $this->title,
        'poster' => $this->poster,
        'duration' => $this->duration,
        'releaseDate' => $this->releaseDate,
        'languages' => $this->langues ?? [],
    ];
}

}