<?php

namespace Tests\Browser;

use App\Helpers\StringUtils;
use Database\Seeders\PaymentMethodSeed;
use Exception;
use Faker\Generator;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase 
{
    use DatabaseMigrations;

    // setup
    public function setUp(): void
    {
        parent::setUp();
        
        // check .env file
        if (!file_exists('.env')) {
            throw new Exception('The .env file does not exist');
        }

        // check APP_URL
        if (!env('APP_URL')) {
            throw new Exception('The APP_URL is not set in the .env file');
        }
        sleep(10);
        // check .env.backup file
        if (!file_exists('.env.backup')) {
            throw new Exception('Please create a dusk environment file by copying the .env file to .env.dusk.'. env('APP_ENV'));
        }

        // get DB_DATABASE from .env.backup
        $db_database = StringUtils::getEnvValue('.env.backup', 'DB_DATABASE');
        if(!$db_database){
            throw new Exception('The DB_DATABASE is not set in the .env.dusk.'.env('APP_ENV').' file');
        }

        // get DB_DATABASE from .env
        $db_database_dusk = env('DB_DATABASE');

        if($db_database == $db_database_dusk){
            throw new Exception('Please create a test database before running the tests');
        }



       
    }
   
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        (new PaymentMethodSeed)->run();
        // run EventFactory to create a new event
        $event = \App\Models\Event::factory()->withTicketTypes(5)->withEventDays(1)->withPaymentMethods()->create();

        $this->browse(function (Browser $browser) use ($event)  {

            $browser->visit('/' . $event->id)
                    ->assertTitle($event->name)
                    ->assertSee("Select your Ticket")
                    ->click("button[type='submit']")
                    ->assertSee("Please select at least one ticket")
                    ->assertSee($event->ticket_types->first()->name)
                    ->click("button[type='submit']")
                    ->assertSee("Please select at least one ticket")
                    ->value("input[name='quantity[]']", 1)
                    ->click("button[type='submit']")
                    ->assertSee("Please select a payment method")
                    ->value("input[name='quantity[]']", 1)
                    ->assertSee($event->payment_methods()->first()->payment_method->name)
                    ->click("input[name='payment_method']");
        $browser->driver->executeScript('window.scrollTo(0, document.body.scrollHeight);');
        
        $browser->click("button[type='submit']")
                ->assertSee("Payment Method")
                ->assertSee("Upload receipt")
                ->type("input[name='name']", "John Doe")
                ->type("input[name='phone_number']", "01156052920")
                ->type("input[name='email']", "example@xxx.com")
                ->click("input[type='submit']")
                ->assertSee("The receipt field is required.")
                ->attach("input[name='receipt']", storage_path('app/assets/examples/sample.pdf'))
                ->click("input[type='submit']")
                ->assertSee("The receipt must be a file of type: png, jpg, jpeg.")
                ->attach("input[name='receipt']", storage_path('app/assets/examples/sample.png'))
                ->click("input[type='submit']")
                ->assertSee("Thank you for registering at " . $event->name);

        });

        // $event->posts()->each(function($post){
        //     $post->ticket()->each(function($ticket){
        //         $ticket->forceDelete();
        //     });
        //     $post->forceDelete();
        // });

        // $event->payment_methods()->each(function($payment_method){
            // $payment_method->forceDelete();
        // });
        // $event->event_days()->each(function($event_day){
        //     $event_day->forceDelete();
        // });
        // $event->ticket_types()->each(function($ticket_type){
        //     $ticket_type->forceDelete();
        // });
        // $event->forceDelete();
    }
}
