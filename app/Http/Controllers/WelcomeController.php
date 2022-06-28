<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class WelcomeController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     * NOTE: Not used it but might use it in future, kept just for reference
     */
    public function boot()
    {
        Http::macro('packt', function () {
            return Http::withHeaders([
                'Authorization' => 'Bearer T7c9ps0cTQu1tBZXGspz99dGarrUQCmht5XrfXiP',
            ])->baseUrl('https://api.packt.com/api/v1');
        });

        // Use it as $response = Http::github()->get('/');
    }

    /**
     * Show the books list to the user.
     *
     * @return \Illuminate\View\View
     */
    public function render_books($page_nos = 1){
        // @todo Get limit from config in future
        $books = $this->get_books( $page_nos, $limit = 8);
        if($books){
            return view('welcome', [
                'books' => $books,
                'current_page' => $page_nos
            ]);
        }
        else{
            return view('error', [
                'message' => 'mismatched token'
            ]);
        }
    }

    public function render_image($product_id){
        $image = $this->get_image($product_id);
        $content_type = $image->header('Content-Type');
        if($content_type){
            $content_type = 'image/png';
        }

        return response($image)
            ->header('Content-Type', $content_type);
    }


    /**
     * Show the books list to the user.
     *
     * @return a response of books with Image
     * NOTE: This can be done completely
     */
    protected function get_books($page_nos ,$limit)
    {
        // lets hit the packt api and test
        $response = Http::withToken(config('welcome.token'))->get(config('welcome.products_url'), ['limit' => $limit, 'page' => $page_nos]);

        // Determine if the status code is >= 200 and < 300...
        $response->successful();
        if($response->successful()){
            $books_data = $response->json();
            $books = $books_data['products'];
            /**
             *  attempt to get the images now but get them by ajax in future to speed up
             *  or include the image path in the api itself(*recommended)
             * NOTE: I have kept the below code just to showcase the efforts put into it. This will be deleted in future
             */
            // for($i = 0; $i < count($books); $i++){
            //     $image = $this->get_image($books[$i]['id']);
            //     if($image){
            //         $books_data['products'][$i]['image'] = base64_encode($image);
            //     }
            // }

            return $books_data;
        }
        else{
            // send some error
            // Determine if the status code is >= 400...
                // $response->failed();

            // Determine if the response has a 400 level status code...
                // $response->clientError();

            // Determine if the response has a 500 level status code...
                // $response->serverError();
            // Immediately execute the given callback if there was a client or server error...
                // $response->onError(callable $callback);
        }

    }



    protected function get_image($product_id){
        $img_response = Http::withToken(config('welcome.token'))->get(config('welcome.products_url').$product_id.'/cover/small', []);

        if($img_response->successful()){
            return $img_response;
        }
        else{
            // may be set a defualt image later
            return false;
        }
    }

}
