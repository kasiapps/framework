<?php

use Kasi\SerializableClosure\Serializers;
use Kasi\SerializableClosure\UnsignedSerializableClosure;

dataset('serializers', function () {
    foreach ([Serializers\Native::class, Serializers\Signed::class, UnsignedSerializableClosure::class] as $serializer) {
        $serializerShortName = (new ReflectionClass($serializer))->getShortName();

        if ($serializer != UnsignedSerializableClosure::class) {
            $serializerShortName = 'SerializableClosure > '.$serializerShortName;
        }

        yield $serializerShortName => function () use ($serializer) {
            test()->serializer = $serializer;

            return $serializer;
        };
    }
});
