<?php

#use Laravel\Lumen\Testing\DatabaseMigrations;
#use Laravel\Lumen\Testing\DatabaseTransactions;

class ControllerTest extends TestCase
{
    /**
     * @test
     */
    public function index_should_be_hello_world()
    {
        $this->get('/');

        $this->assertEquals(
            json_encode(['hello'=>'world']), $this->response->getContent()
        );
    }
    /**
     * @test
     */
    public function methods_should_return_200()
    {
        $this->get('/people')->seeStatusCode(200);
    }
}
