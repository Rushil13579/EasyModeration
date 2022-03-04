<?php

namespace Rushil13579\EasyModeration\Discord;

use pocketmine\Server;
use Rushil13579\EasyModeration\Discord\task\DiscordPost;

class DiscordManager {

    public static function postWebhook(string $webhook, string $content, string $username, array $embed = []): void {
        $data = [
            "username" => $username,
            "content" => $content];
        if(!empty($embed)) {
            $data["embeds"] = $embed;
            unset($data["content"]);
        } else {
            $msg = $data["content"];
            $msg = str_replace("@everyone", "(@)everyone", $msg);
            $msg = str_replace("@here", "(@)here", $msg);
            $data["content"] = $msg;
        }
        $con = json_encode($data);
        $post = new DiscordPost($webhook, $con);
        Server::getInstance()->getAsyncPool()->submitTask($post);
    }
}