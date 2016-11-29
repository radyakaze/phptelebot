# PHPTelebot
Telegram bot framework written in PHP

## Features

* Simple, easy to use.
* Support Long Polling and Webhook.

## Requirements

- [cURL](http://php.net/manual/en/book.curl.php)
- PHP 5.4+
- Telegram Bot API Token - Talk to [@BotFather](https://telegram.me/@BotFather) and generate one.

## Installation

### Using [Composer](https://getcomposer.org)

To install PHPTelebot with Composer, just add the following to your `composer.json` file:

```json
{
    "require": {
        "radyakaze/phptelebot": "*"
    }
}
```

or by running the following command:

```shell
composer require radyakaze/phptelebot
```

Composer installs autoloader at `./vendor/autoloader.php`. to include the library in your script, add:

```php
require_once 'vendor/autoload.php';
```

### Install from source

Download the PHP library from Github, then include `PHPTelebot.php` in your script:

```php
require_once '/path/to/phptelebot/src/PHPTelebot.php';
```


## Usage


### Creating a simple bot
```php
<?php

require_once './src/PHPTelebot.php';
$bot = new PHPTelebot('TOKEN', 'BOT_USERNAME'); // Bot username is optional, its required for handle command that contain username (/command@username) like on a group.

// Simple command
$bot->cmd('*', 'Hi, human! I am a bot.');

// Simple echo command
$bot->cmd('/echo|/say', function ($text) {
    if ($text == '') {
        $text = 'Command usage: /echo [text] or /say [text]';
    }
    return Bot::sendMessage($text);
});

// Simple whoami command
$bot->cmd('/whoami|!whoami', function () {
    // Get message properties
    $message = Bot::message();
    $name = $message['from']['first_name'];
    $userId = $message['from']['id'];
    $text = 'You are <b>'.$name.'</b> and your ID is <code>'.$userId.'</code>';
    $options = [
        'parse_mode' => 'html',
        'reply' => true
    ];

    return Bot::sendMessage($text, $options);
});

$bot->run();
```
Then run
```shell
php file.php
```

You can also see my other [sample](https://github.com/radyakaze/phptelebot/blob/master/sample.php).

*NOTE:*
- If function parameters is more than one, PHPTelebot will split text by space.
- If you don't set chat_id on options bot will send message to current chat.
- If you add option **reply => true**, bot will reply current message (Only work if you don't set custom chat_id and reply_to_mesage_id).

## Commands

Use `$bot->cmd(<command>, <function>)` to handle command.
```php
// simple answer
$bot->cmd('*', 'I am a bot');

// catch multiple commands
$bot->cmd('/start|/help', function () {
   // Do something here.
});

// call a function name
function googleSearch($search) {
   // Do something here.
}
$bot->cmd('/google', 'googleSearch');
```
Use **&#42;** to catch any command.

#### File upload
This code will send a photo to users when type command **/upload**.
```php
// Simple photo upload
$bot->cmd('/upload', function () {
    $file = '/path/to/photo.png'; // File path, file id, or url.
    return Bot::sendPhoto($file);
});
```

## Events

Use `$bot->on(<event>, <function>)` to handle all possible PHPTelebot events.

To handle inline message, just add:
```php
$bot->on('inline', function($text) {
    $results[] = [
        'type' => 'article',
        'id' => 'unique_id1',
        'title' => $text,
        'message_text' => 'Lorem ipsum dolor sit amet',
    ];
    $options = [
        'cache_time' => 3600,
    ];

    return Bot::answerInlineQuery($results, $options);
});
```
Also, you can catch multiple events:
```php
$bot->on('sticker|photo|document', function() {
  // Do something here.
 });
```

## Supported events:
- **&#42;** - any type of message
- **text** – text message
- **audio** – audio file
- **voice** – voice message
- **document** – document file (any kind)
- **photo** – photo
- **sticker** – sticker
- **video** – video file
- **contact** – contact data
- **location** – location data
- **venue** – venue data
- **edited** – edited message
- **pinned_message** – message was pinned
- **new_chat_member** – new member was added
- **left_chat_member** – member was removed
- **new_chat_title** – new chat title
- **new_chat_photo** – new chat photo
- **delete_chat_photo** – chat photo was deleted
- **group_chat_created** – group has been created
- **channel_chat_created** – channel has been created
- **supergroup_chat_created** – supergroup has been created
- **migrate_to_chat_id** – group has been migrated to a supergroup
- **migrate_from_chat_id** – supergroup has been migrated from a group
- **inline** - inline message
- **callback** - callback message
- **game** - game
- **channel** - channel
- **edited_channel** - edited channel post

## Command with custom regex *(advanced)*

Create a command: */regex string number*
```php
$bot->regex('/^\/regex (.*) ([0-9])$/i', function($matches) {
    // Do something here.
});
```

## Methods

### PHPTelebot Methods
##### `cmd(<command>, <answer>)`
Handle a command.
##### `on(<event>, <answer>)`
Handles events.
##### `regex(<regex>, <answer>)`
Create custom regex for command.
##### `Bot::type()`
Return [message event](#supported-events) type.
##### `Bot::message()`
Get [message properties](https://core.telegram.org/bots/api#message).

### Telegram Methods
PHPTelebot use standard [Telegram Bot API](https://core.telegram.org/bots/api#available-methods) method names.
##### `Bot::getMe()` [?](https://core.telegram.org/bots/api#getme)
A simple method for testing your bot's auth token.
##### `Bot::sendMessage(<text>, <options>)` [?](https://core.telegram.org/bots/api#sendmessage)
Use this method to send text messages.
##### `Bot::forwardMessage(<options>)` [?](https://core.telegram.org/bots/api#forwardmessage)
Use this method to forward messages of any kind.
##### `Bot::sendPhoto(<file path | file id | url>, <options>)` [?](https://core.telegram.org/bots/api#sendphoto)
Use this method to send a photo.
##### `Bot::sendVideo(<file path | file id | url>, <options>)` [?](https://core.telegram.org/bots/api#sendvideo)
Use this method to send a video.
##### `Bot::sendAudio(<file path | file id | url>, <options>)` [?](https://core.telegram.org/bots/api#sendaudio)
Use this method to send a audio.
##### `Bot::sendVoice(<file path | file id | url>, <options>)` [?](https://core.telegram.org/bots/api#sendvoice)
Use this method to send a voice message.
##### `Bot::sendDocument(<file path | file id | url>, <options>)` [?](https://core.telegram.org/bots/api#senddocument)
Use this method to send a document.
##### `Bot::sendSticker(<file path | file id | url>, <options>)` [?](https://core.telegram.org/bots/api#sendsticker)
Use this method to send a sticker.
##### `Bot::sendLocation(<options>)` [?](https://core.telegram.org/bots/api#sendlocation)
Use this method to send point on the map.
##### `Bot::sendVenue(<options>)` [?](https://core.telegram.org/bots/api#sendvenue)
Use this method to send information about a venue.
##### `Bot::sendContact(<options>)` [?](https://core.telegram.org/bots/api#sendcontact)
Use this method to send phone contacts.
##### `Bot::sendAction(<action>, <options>)` [?](https://core.telegram.org/bots/api#sendchataction)
Use this method when you need to tell the user that something is happening on the bot's side.
##### `Bot::getUserProfilePhotos(<user id>, <options>)` [?](https://core.telegram.org/bots/api#getuserprofilephotos)
Use this method to get a list of profile pictures for a user.
##### `Bot::getFile(<file id>)` [?](https://core.telegram.org/bots/api#getfile)
Use this method to get basic info about a file and prepare it for downloading. For the moment, bots can download files of up to 20MB in size.
##### `Bot::answerInlineQuery(<array of results>, <options>)` [?](https://core.telegram.org/bots/api#answerinlinequery)
Use this method to send answers to an inline query.
##### `Bot::answerCallbackQuery(<text>, <options>)` [?](https://core.telegram.org/bots/api#answercallbackquery)
Use this method to send answers to callback queries sent from inline keyboards.
##### `Bot::getChat(<chat_id>)` [?](https://core.telegram.org/bots/api#getchat)
Use this method to get up to date information about the chat.
##### `Bot::leaveChat(<chat_id>)` [?](https://core.telegram.org/bots/api#leavechat)
Use this method for your bot to leave a group, supergroup or channel.
##### `Bot::getChatAdministrators(<chat_id>)` [?](https://core.telegram.org/bots/api#getchatadministrators)
Use this method to get a list of administrators in a chat.
##### `Bot::getChatMembersCount(<chat_id>)` [?](https://core.telegram.org/bots/api#getchatmemberscount)
Use this method to get the number of members in a chat.
##### `Bot::getChatMember(<options>)` [?](https://core.telegram.org/bots/api#getchatmember)
Use this method to get information about a member of a chat.
##### `Bot::kickChatMember(<options>)` [?](https://core.telegram.org/bots/api#kickchatmember)
Use this method to kick a user from a group or a supergroup.
##### `Bot::unbanChatMember(<options>)` [?](https://core.telegram.org/bots/api#unbanchatmember)
Use this method to unban a previously kicked user in a supergroup.
##### `Bot::editMessageText(<options>)` [?](https://core.telegram.org/bots/api#editmessagetext)
Use this method to edit text messages sent by the bot or via the bot (for inline bots).
##### `Bot::editMessageCaption(<options>)` [?](https://core.telegram.org/bots/api#editmessagecaption)
Use this method to edit captions of messages sent by the bot or via the bot (for inline bots).
##### `Bot::editMessageReplyMarkup(<options>)` [?](https://core.telegram.org/bots/api#editmessagereplymarkup)
Use this method to edit only the reply markup of messages sent by the bot or via the bot (for inline bots).
#####  `Bot::sendGame(<game short name>, <options>)` [?](https://core.telegram.org/bots/api#sendgame)
Use this method to send a game.
##### `Bot::setGameScore(<options>)` [?](https://core.telegram.org/bots/api#setgamescore)
Use this method to set the score of the specified user in a game.
##### `Bot::getGameHighScores(<user id>, <options>)` [?](https://core.telegram.org/bots/api#getgamehighscores)
Use this method to get data for high score tables.

## Webhook installation
Open via browser `https://api.telegram.org/bot<BOT TOKEN>/setWebhook?url=https://yourdomain.com/your_bot.php`
