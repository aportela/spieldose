<?php

declare(strict_types=1);

namespace Spieldose\Entities;

enum ArtistRelation: string
{
    case IMAGE = "221132e9-e30e-43f2-a741-15afc4c5fa7c";
    case OFFICIAL_HOMEPAGE = "fe33d22f-c3b0-4d68-bd53-a856badf2b15";
    case LASTFM = "08db8098-c0df-4b78-82c3-c8697b4bba7f";
    case WIKIDATA = "689870a4-a1e4-4912-b17f-7b2664215698";
    case WIKIPEDIA = "29651736-fa6d-48e4-aadc-a557c6add1cb";
}
