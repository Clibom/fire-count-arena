<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

uses(WebTestCase::class);

it('displays the fire count form', function () {
    $client = static::createClient();
    $crawler = $client->request('GET', '/fire-count');

    expect($client->getResponse()->isSuccessful())->toBeTrue()
        ->and($crawler->filter('form')->count())->toBe(1)
        ->and($crawler->filter('input[name*="email"]')->count())->toBe(1)
        ->and($crawler->filter('input[name*="adultsCount"]')->count())->toBe(1)
        ->and($crawler->filter('input[name*="childrenCount"]')->count())->toBe(1);
});

it('submits form and redirects with success message', function () {
    $client = static::createClient();
    $crawler = $client->request('GET', '/fire-count');

    $form = $crawler->selectButton('Submit')->form([
        'fire_count[email]' => 'test@example.com',
        'fire_count[adultsCount]' => 2,
        'fire_count[childrenCount]' => 1,
    ]);

    $client->submit($form);

    expect($client->getResponse()->isRedirect('/fire-count'))->toBeTrue();

    $client->followRedirect();

    expect($client->getResponse()->getContent())->toContain('success');
});

it('shows validation errors for invalid email', function () {
    $client = static::createClient();
    $crawler = $client->request('GET', '/fire-count');

    $form = $crawler->selectButton('Submit')->form([
        'fire_count[email]' => 'invalid-email',
        'fire_count[adultsCount]' => 0,
        'fire_count[childrenCount]' => 0,
    ]);

    $crawler = $client->submit($form);

    expect($client->getResponse()->isSuccessful())->toBeTrue();
});

it('returns 404 for invalid uuid', function () {
    $client = static::createClient();
    $client->request('GET', '/fire-count/invalid-uuid-that-does-not-exist');

    expect($client->getResponse()->getStatusCode())->toBe(404);
});
