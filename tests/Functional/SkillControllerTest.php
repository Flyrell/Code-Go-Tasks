<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Tests\Functional;

use App\Repository\SkillRepository;
use App\Tests\AppTestCase;

class SkillControllerTest extends AppTestCase
{
    public function testSomething()
    {
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/skill/new');
        $testSkillName = 'Test Skill';
        $response = $client->getResponse();
        $responseCode = $response->getStatusCode();

        if (500 === $responseCode) {
            $this->logExceptionToFile('SkillControllerTest.exception.html', $response->getContent());
        }

        $this->assertSame(200, $responseCode);
        $this->assertContains('Create new Skill', $crawler->filter('h1')->text());

        $form = $crawler->filter('form[name="new_skill"]')->form();

        $form['new_skill[name]'] = $testSkillName;

        // Submit form and create item
        $crawler = $client->submit($form);

        // Check that we were redirected properly
        $this->assertEquals('http://localhost/skill/', $crawler->getUri());

        // Check that skill was really created
        $this->assertEquals('Skill index', $crawler->filter('h1')->text());

        $firstTableCellText = $crawler->filter('table > tbody > tr > td')->text();

        $this->assertContains($testSkillName, $firstTableCellText);

        /** @var SkillRepository $skillRepository */
        $skillRepository = self::$container->get(SkillRepository::class);

        // Also check in the database
        $skills = $skillRepository->findBy(['name' => $testSkillName], ['level' => 'desc']);
        $skillIds = [];

        foreach ($skills as $skill) {
            $skillIds[] = $skill->getId();
        }

        $this->assertEquals(5, count($skills));

        // Find edit link
        $viewLink = $crawler->filter('table > tbody > tr > td > a.delete-skill')->link();

        $crawler = $client->request('GET', $viewLink->getUri());

        // Check that we were redirected properly
        $this->assertEquals('http://localhost/skill/'.$skillIds[0], $crawler->getUri());

        // Get the title containing the Skill name
        $titleWithSkillName = $crawler->filter('h2')->text();

        // Make sure it contains our skill name
        $this->assertContains($testSkillName, $titleWithSkillName);

        // Get the delete form
        $form = $crawler->filter('form[name="delete_skill"]')->form();

        // Delete the skill
        $crawler = $client->submit($form);

        // Check that we were redirected properly
        $this->assertEquals('http://localhost/skill/', $crawler->getUri());

        // Check in the database that the skill was deleted
        $skill = $skillRepository->findOneBy(['name' => $testSkillName]);

        $this->assertNull($skill);
    }
}
