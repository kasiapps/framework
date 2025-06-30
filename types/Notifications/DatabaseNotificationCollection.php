<?php

use Kasi\Notifications\DatabaseNotification;
use Kasi\Notifications\DatabaseNotificationCollection;

use function PHPStan\Testing\assertType;

class CustomNotification extends DatabaseNotification
{
    //
}

/**
 * @extends DatabaseNotificationCollection<int, CustomNotification>
 */
class CustomNotificationCollection extends DatabaseNotificationCollection
{
    //
}

$databaseNotificationsCollection = DatabaseNotification::all();
assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Notifications\DatabaseNotification>', $databaseNotificationsCollection);

$customNotificationsCollection = CustomNotification::all();
assertType('Kasi\Database\Eloquent\Collection<int, CustomNotification>', $customNotificationsCollection);
