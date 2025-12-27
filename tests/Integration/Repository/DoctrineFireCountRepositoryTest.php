<?php

declare(strict_types=1);

use App\Domain\Model\FireCount;
use App\Domain\Repository\FireCountRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

uses(KernelTestCase::class);

beforeEach(function () {
    self::bootKernel();
    $this->repository = self::getContainer()->get(FireCountRepositoryInterface::class);
});

it('saves and retrieves fire count by id', function () {
    $uniqueId = 'test-uuid-' . uniqid();

    $fireCount = new FireCount(
        id: $uniqueId,
        email: 'integration@test.com',
        adultsCount: 4,
        childrenCount: 3,
        createdAt: new DateTimeImmutable(),
    );

    $this->repository->save($fireCount);

    $retrieved = $this->repository->findById($uniqueId);

    expect($retrieved)->not->toBeNull()
        ->and($retrieved->id)->toBe($uniqueId)
        ->and($retrieved->email)->toBe('integration@test.com')
        ->and($retrieved->adultsCount)->toBe(4)
        ->and($retrieved->childrenCount)->toBe(3);
});

it('returns null for non-existent id', function () {
    $result = $this->repository->findById('non-existent-uuid-xyz');

    expect($result)->toBeNull();
});
