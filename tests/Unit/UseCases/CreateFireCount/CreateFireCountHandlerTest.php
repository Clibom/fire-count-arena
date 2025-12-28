<?php

declare(strict_types=1);

use App\Domain\Model\FireCount;
use App\Domain\Repository\FireCountRepositoryInterface;
use App\UseCases\CreateFireCount\CreateFireCountCommand;
use App\UseCases\CreateFireCount\CreateFireCountHandler;
use App\UseCases\SendFireCountEmail\SendFireCountEmailCommand;
use App\UseCases\SendFireCountEmail\SendFireCountEmailHandler;

beforeEach(function () {
    $this->repository = Mockery::mock(FireCountRepositoryInterface::class);
    $this->emailHandler = Mockery::mock(SendFireCountEmailHandler::class);
    $this->handler = new CreateFireCountHandler($this->repository, $this->emailHandler);
});

afterEach(function () {
    Mockery::close();
});

it('creates a fire count with correct data', function () {
    $startTime = new DateTimeImmutable('10:00');
    $endTime = new DateTimeImmutable('12:00');

    $command = new CreateFireCountCommand(
        email: 'test@example.com',
        adultsCount: 2,
        childrenCount: 1,
        startTime: $startTime,
        endTime: $endTime,
    );

    $this->repository->shouldReceive('save')
        ->once()
        ->with(Mockery::on(function (FireCount $fireCount) use ($startTime, $endTime) {
            return $fireCount->email === 'test@example.com'
                && $fireCount->adultsCount === 2
                && $fireCount->childrenCount === 1
                && $fireCount->startTime == $startTime
                && $fireCount->endTime == $endTime;
        }));

    $this->emailHandler->shouldReceive('handle')
        ->once()
        ->with(Mockery::type(SendFireCountEmailCommand::class));

    $result = $this->handler->handle($command);

    expect($result)->toBeInstanceOf(FireCount::class)
        ->and($result->email)->toBe('test@example.com')
        ->and($result->adultsCount)->toBe(2)
        ->and($result->childrenCount)->toBe(1)
        ->and($result->startTime)->toEqual($startTime)
        ->and($result->endTime)->toEqual($endTime);
});

it('generates a valid UUID for id', function () {
    $command = new CreateFireCountCommand(
        email: 'test@example.com',
        adultsCount: 0,
        childrenCount: 0,
        startTime: new DateTimeImmutable('10:00'),
        endTime: new DateTimeImmutable('12:00'),
    );

    $this->repository->shouldReceive('save')->once();
    $this->emailHandler->shouldReceive('handle')->once();

    $result = $this->handler->handle($command);

    // UUID v4 format validation
    expect($result->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

it('sends email with correct fire count id after saving', function () {
    $command = new CreateFireCountCommand(
        email: 'recipient@example.com',
        adultsCount: 3,
        childrenCount: 2,
        startTime: new DateTimeImmutable('10:00'),
        endTime: new DateTimeImmutable('12:00'),
    );

    $savedFireCount = null;

    $this->repository->shouldReceive('save')
        ->once()
        ->andReturnUsing(function (FireCount $fc) use (&$savedFireCount) {
            $savedFireCount = $fc;
        });

    $this->emailHandler->shouldReceive('handle')
        ->once()
        ->with(Mockery::on(function (SendFireCountEmailCommand $cmd) use (&$savedFireCount) {
            return $cmd->email === 'recipient@example.com'
                && $cmd->fireCountId === $savedFireCount->id
                && $cmd->adultsCount === 3
                && $cmd->childrenCount === 2;
        }));

    $this->handler->handle($command);
});
