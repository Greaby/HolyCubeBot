<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';

use DG\Twitter\Twitter;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');


function getPlayerTwitter($id) {
    switch ($id) {
        case 1:
            return "@Aurelien_Sama";
        case 2:
            return "@AlkasymMc";
        case 3:
            return "@AlpZz80";
        case 4:
            return "@AypierreMc";
        case 5:
            return "@Bahason_";
        case 6:
            return "@clintwood245";
        case 7:
            return "@DavLec1";
        case 8:
            return "@Edorocky";
        case 9:
            return "@goldawnyt";
        case 10:
            return "@TheGuill84";
        case 11:
            return "@Ika_vg";
        case 12:
            return "@JimmyBoyyy_";
        case 13:
            return "@KeyOps14";
        case 14:
            return "@kisukeisflo";
        case 15:
            return "@LetoVII_Gaming";
        case 16:
            return "@Magicknup";
        case 17:
            return "@majorrrsalty";
        case 18:
            return "@Mayu_Kow";
        case 19:
            return "@MrMLDEG";
        case 20:
            return "@_OraNN_";
        case 21:
            return "@RedToxx";
        case 22:
            return "@R3li3nt";
        case 23:
            return "@The_Boune";
        case 24:
            return "@Roi_Louis_";
        case 25:
            return "@Steelorse";
        case 26:
            return "@Tungstene74";
        case 27:
            return "@Letsaudric1";
        case 28:
            return "@Vartac_";
        case 29:
            return "@TheWotan1283";
        case 30:
            return "@zakarum78";
        case 31:
            return "@Zanzag_Video";
        case 32:
            return "@Zedh74mc";
        case 33:
            return "@Zeptuna";
        case 34:
            return "@Nems_Mt";
        case 35:
            return "@MylaCraft";
        case 36:
            return "@Shoukachu";
    }

    return "Holycube";
}


function getPlayer($list, $id) {
    foreach ($list as $player) {
        if($player["id"] === $id) {
            return $player;
        }
    }

    return null;
}


function sendTwitter($message) {
    try {
        echo $message . "<br>";

        if($_ENV["ENVIRONMENT"] === "production") {
            $twitter = new Twitter(
                $_ENV["TWITTER_CONSUMER_KEY"], 
                $_ENV["TWITTER_CONSUMER_SECRET"], 
                $_ENV["TWITTER_ACCESS_TOKEN_KEY"], 
                $_ENV["TWITTER_ACCESS_TOKEN_SECRET"]
            );
            $twitter->send($message);
        }
        
    } catch (exception $e) {
        echo $e->getMessage();
    }
   
}



$filename_players = "data/" . $_ENV["FILENAME_PLAYERS"];
$filename_videos = "data/" . $_ENV["FILENAME_VIDEOS"];


// get last API data
$last_players = null;
$last_videos = null;

if(file_exists($filename_players)) {
    $last_players = json_decode(file_get_contents($filename_players), true);
}

if(file_exists($filename_videos)) {
    $last_videos = json_decode(file_get_contents($filename_videos), true);
}



// Fetch API
$players = json_decode(file_get_contents("https://api.holycube.fr/players"), true);
$videos = json_decode(file_get_contents("https://api.holycube.fr/videos"), true);



// send last videos
if($last_videos !== null) {

    $ids = array_map(function($video) {
        return $video["videoId"];
    }, $last_videos["hydra:member"]);


    foreach ($videos["hydra:member"] as $video) {
        if(!in_array($video["videoId"], $ids)) {

            $playerID = (int)str_replace("/players/", "", $video["player"]);
            $twitter = getPlayerTwitter($playerID);

            sendTwitter(join(" ", ["Nouvelle vidéo de", $twitter, $video["title"], "https://www.youtube.com/watch?v=".$video["videoId"], "#HolyCube", "#Minecraft"]));
        }
    }
}


// send player is live
if($last_players !== null) {
    foreach ($players["hydra:member"] as $player) {
        $last_players_data = getPlayer($last_players["hydra:member"], $player["id"]);
        
        if(!empty($last_players_data) and !empty($player["twitchName"]) and $player["isLiveHolycube"] and !$last_players_data["isLiveHolycube"]) {
            $twitter = getPlayerTwitter($player["id"]);
            sendTwitter(join(" ", ["En live", $twitter, $player["liveName"], "https://www.twitch.tv/". $player["twitchName"], "#HolyCube", "#Minecraft"]));
        }
    }
}


// save API data
$players_file = fopen($filename_players, 'w');
fwrite($players_file, json_encode($players));
fclose($players_file);

$videos_file = fopen($filename_videos, 'w');
fwrite($videos_file, json_encode($videos));
fclose($videos_file);