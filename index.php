<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';

use DG\Twitter\Twitter;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');


function getPlayer($list, $id) {
    foreach ($list as $player) {
        if($player["@id"] === $id) {
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
            $player = getPlayer($players["hydra:member"], $video["player"]);
            sendTwitter(join(" ", ["Nouvelle vidéo de", "@".$player["twitterName"], $video["title"], "https://www.youtube.com/watch?v=".$video["videoId"], "#HolyCube", "#Minecraft"]));
        }
    }
}


// send player is live
if($last_players !== null) {
    foreach ($players["hydra:member"] as $player) {
        $last_players_data = getPlayer($last_players["hydra:member"], $player["@id"]);
        
        if(!empty($last_players_data) and !empty($player["twitchName"]) and $player["isLiveHolycube"] and !$last_players_data["isLiveHolycube"]) {
            sendTwitter(join(" ", ["En live", "@".$player["twitterName"], $player["liveName"], "https://www.twitch.tv/". $player["twitchName"], "#HolyCube", "#Minecraft"]));
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