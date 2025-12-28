<?php

declare(strict_types=1);

use App\Domain\Model\FireCount;

it('calculates total people correctly', function () {
    $fireCount = new FireCount(
        id: 'test-uuid',
        email: 'test@example.com',
        adultsCount: 2,
        childrenCount: 3,
        startTime: new DateTimeImmutable('10:00'),
        endTime: new DateTimeImmutable('12:00'),
        createdAt: new DateTimeImmutable(),
    );

    expect($fireCount->getTotalPeople())->toBe(5);
});

it('returns zero total when both counts are zero', function () {
    $fireCount = new FireCount(
        id: 'test-uuid',
        email: 'test@example.com',
        adultsCount: 0,
        childrenCount: 0,
        startTime: new DateTimeImmutable('10:00'),
        endTime: new DateTimeImmutable('12:00'),
        createdAt: new DateTimeImmutable(),
    );

    expect($fireCount->getTotalPeople())->toBe(0);
});

it('stores all properties correctly', function () {
    $createdAt = new DateTimeImmutable('2024-01-15 10:30:00');
    $startTime = new DateTimeImmutable('10:00');
    $endTime = new DateTimeImmutable('12:00');

    $fireCount = new FireCount(
        id: 'abc-123-uuid',
        email: 'user@domain.com',
        adultsCount: 4,
        childrenCount: 2,
        startTime: $startTime,
        endTime: $endTime,
        createdAt: $createdAt,
    );

    expect($fireCount->id)->toBe('abc-123-uuid')
        ->and($fireCount->email)->toBe('user@domain.com')
        ->and($fireCount->adultsCount)->toBe(4)
        ->and($fireCount->childrenCount)->toBe(2)
        ->and($fireCount->startTime)->toBe($startTime)
        ->and($fireCount->endTime)->toBe($endTime)
        ->and($fireCount->createdAt)->toBe($createdAt);
});

it('is immutable after creation', function () {
    $fireCount = new FireCount(
        id: 'test-uuid',
        email: 'test@example.com',
        adultsCount: 1,
        childrenCount: 1,
        startTime: new DateTimeImmutable('10:00'),
        endTime: new DateTimeImmutable('12:00'),
        createdAt: new DateTimeImmutable(),
    );

    // Verify readonly properties cannot be modified
    $reflection = new ReflectionClass($fireCount);
    expect($reflection->isReadOnly())->toBeTrue();
});
