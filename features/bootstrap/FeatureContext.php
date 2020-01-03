<?php

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Mink\Mink;
use DataMod\Address;
use DataMod\User;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, MinkAwareContext
{
    private $mink;
    private $minkParameters;

    public function setMink(Mink $mink)
    {
        $this->mink = $mink;
    }

    public function setMinkParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @Then I should see the users age on the page
     */
    public function iShouldSeeTheUsersAgeOnThePage()
    {
        $age = User::getValue('age');

        $this->mink->assertSession()->pageTextContains('age: ' . $age . ' years');
    }

    /**
     * @Given I have the user fixture
     */
    public function iHaveTheUserFixture()
    {
        User::delete(['id' => '!NULL']);

        User::createFixture([
            'name' => 'Wahab Qureshi',
            'date of birth' => '10-05-1989',
            'age' => 29,
            'hobby' => 'swimming',
        ]);
    }

    /**
     * @Given I have the address fixture
     */
    public function iHaveTheAddressFixture()
    {
        Address::delete(['id' => '!NULL']);

        Address::createFixture();
    }
}
