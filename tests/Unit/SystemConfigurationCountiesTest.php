<?php

namespace spkm\isams\Tests\Unit;

use Tests\TestCase;
use spkm\isams\School;
use spkm\isams\Wrappers\County;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Controllers\CountyController;

class SystemConfigurationCountiesTest extends TestCase
{
    /**
     * @var School
     */
    protected $school;

    public function __construct()
    {
        parent::__construct();

        $this->school = new School;
    }

    /** @test */
    public function it_returns_all_counties_as_a_collection_of_county_classes_stored_in_cache()
    {
        $counties = (new CountyController($this->school))->index();

        foreach ($counties as $county):
            $this->assertTrue(is_a($county, County::class));

            $properties = ['id', 'description', 'listType', 'name'];
            foreach ($properties as $property):
                $this->assertTrue(array_key_exists($property, $county));
            endforeach;
        endforeach;

        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'counties.index'));
    }

    /** @test */
    public function it_creates_a_new_county()
    {
        $response = (new CountyController($this->school))->store([
            'name' => 'MyNewCounty',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    public function it_deletes_a_county()
    {
        //Create it
        $newCounty = 'MyNewCounty';
        (new CountyController($this->school))->store([
            'name' => $newCounty,
        ]);

        //Find it
        $counties = (new CountyController($this->school))->index();
        $toDelete = ($this->findCountyByName($newCounty, $counties->toArray()));

        //Delete it
        foreach ($toDelete as $idToDelete):
            $response = (new CountyController($this->school))->destroy($idToDelete);
            $this->assertEquals(200, $response->getStatusCode());
        endforeach;
    }

    /** @test */
    public function it_updates_a_county()
    {
        //Create it
        $newCounty = 'MyNewCounty';
        (new CountyController($this->school))->store([
            'name' => $newCounty,
        ]);

        //Find it
        $counties = (new CountyController($this->school))->index();
        $toUpdate = ($this->findCountyByName($newCounty, $counties->toArray()));

        //Update it
        $renameCounty = 'MySpecialCounty';
        $response = (new CountyController($this->school))->update($toUpdate[0], ['name' => $renameCounty]);
        $this->assertEquals(200, $response->getStatusCode());

        //Find it again
        $counties = (new CountyController($this->school))->index();
        $toDelete = ($this->findCountyByName($renameCounty, $counties->toArray()));

        //Delete it
        foreach ($toDelete as $idToDelete):
            $response = (new CountyController($this->school))->destroy($idToDelete);
            $this->assertEquals(200, $response->getStatusCode());
        endforeach;
    }

    /**
     * Find the elements by name
     *
     * @param string $name
     * @param array $counties
     * @return array
     */
    private function findCountyByName(string $name, array $counties)
    {
        $matches = [];
        foreach ($counties as $element):
            if ($name == $element->name) {
                array_push($matches, $element->id);
            }
        endforeach;

        return $matches;
    }
}
