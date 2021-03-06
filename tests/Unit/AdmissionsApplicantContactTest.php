<?php

namespace spkm\isams\Tests\Unit;

use Tests\TestCase;
use spkm\isams\School;
use spkm\isams\Wrappers\PupilContact;
use spkm\isams\Controllers\AdmissionApplicantContactController;

class AdmissionsApplicantContactTest extends TestCase
{
    /**
     * @var School
     */
    protected $school;

    /**
     * @var array
     */
    protected $properties = [
        'id',
        'address1',
        'address2',
        'address3',
        'contactOnly',
        'country',
        'county',
        'emailAddress',
        'emergencyNotes',
        'forename',
        'middleNames',
        'mobileNumber',
        'postcode',
        'relationship',
        'surname',
        'telephoneNumber',
        'title',
        'town',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->school = new School;
    }

    /** @test */
    public function it_returns_the_specified_applicants_contacts_as_a_collection()
    {
        $schoolId = '2443384966';
        $contacts = (new AdmissionApplicantContactController($this->school))->show($schoolId);

        foreach ($contacts as $contact):
            $this->assertTrue(is_a($contact, PupilContact::class));

            foreach ($this->properties as $property):
                $this->assertTrue(array_key_exists($property, $contact));
            endforeach;
        endforeach;
    }

    /** @test */
    public function it_returns_the_specified_applicant_contact()
    {
        $schoolId = '2443384966';
        $contactId = 2486;

        $contact = (new AdmissionApplicantContactController($this->school))->showContact($schoolId, $contactId);

        $this->assertTrue(is_a($contact, PupilContact::class));

        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($contact, $property));
        endforeach;
    }

    /** @test */
    public function it_creates_a_new_applicant_contact_and_returns_its_id()
    {
        $schoolId = '2443384966';
        $response = (new AdmissionApplicantContactController($this->school))->store($schoolId,[
            'relationship' => 'Father',
            'contactType' => 'Legal Guardian',
            'title' => 'Mr',
            'forename' => 'John',
            'surname' => 'Doe',
            'address1' => 'foo street',
            'postcode' => 'ZZ99 3WZ',
            'country' => 'Cloud',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertNotEmpty(json_decode($response->getContent())->location);
        $this->assertNotEmpty(json_decode($response->getContent())->id);
    }


    /** @test */
    public function it_updates_the_specified_applicant_contact()
    {
        $schoolId = '2443384966';
        $response = (new AdmissionApplicantContactController($this->school))->store($schoolId,[
            'relationship' => 'Father',
            'contactType' => 'Legal Guardian',
            'title' => 'Mr',
            'forename' => 'John',
            'surname' => 'Doe',
            'address1' => 'foo street',
            'postcode' => 'ZZ99 3WZ',
            'country' => 'Cloud',
        ]);

        $contactId = json_decode($response->getContent())->id;
        $changedAttributes = [
            'relationship' => 'Mother',
            'contactType' => 'Home',
            'title' => 'Mrs',
            'forename' => 'Jane',
            'surname' => 'Doe',
            'address1' => 'foo street',
            'postcode' => 'ZZ99 3WZ',
            'country' => 'Cloud2',
        ];

        (new AdmissionApplicantContactController($this->school))->update($schoolId,$contactId, $changedAttributes);

        $contact = (new AdmissionApplicantContactController($this->school))->showContact($schoolId,$contactId);

        $this->assertTrue(is_a($contact, PupilContact::class));
        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($contact, $property));
        endforeach;

        foreach ($changedAttributes as $key => $value):
            $this->assertTrue($contact->$key == $value);
        endforeach;
    }
}
