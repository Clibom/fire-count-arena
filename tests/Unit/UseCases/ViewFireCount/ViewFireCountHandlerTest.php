<?php

declare(strict_types=1);

use App\Domain\Exception\FireCountNotFoundException;
use App\Domain\Model\FireCount;
use App\Domain\Repository\FireCountRepositoryInterface;
use App\UseCases\ViewFireCount\ViewFireCountHandler;
use App\UseCases\ViewFireCount\ViewFireCountQuery;

beforeEach(function () {
    $this->repository = Mockery::mock(FireCountRepositoryInterface::class);
    $this->handler = new ViewFireCountHandler($this->repository);
});

afterEach(function () {
    Mockery::close();
});

it('returns fire count when found by id', function () {
    $expectedFireCount = new FireCount(
        id: 'valid-uuid',
        email: 'test@example.com',
        adultsCount: 2,
        childrenCount: 1,
        createdAt: new DateTimeImmutable(),
    );

    $this->repository->shouldReceive('findById')
        ->with('valid-uuid')
        ->once()
        ->andReturn($expectedFireCount);

    $query = new ViewFireCountQuery('valid-uuid');
    $result = $this->handler->handle($query);

    expect($result)->toBe($expectedFireCount);
});

it('throws exception when id not found', function () {
    $this->repository->shouldReceive('findById')
        ->with('invalid-uuid')
        ->once()
        ->andReturn(null);

    $query = new ViewFireCountQuery('invalid-uuid');

    $this->handler->handle($query);
})->throws(FireCountNotFoundException::class);
