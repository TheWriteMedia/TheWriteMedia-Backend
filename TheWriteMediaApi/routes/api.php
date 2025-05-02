<?php

use App\Http\Controllers\AddOnController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\AuthorReviewsController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\NewsBannerController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\TotalAccumulatedRoyaltyController;
use App\Http\Controllers\UpcomingBookFairController;
use App\Http\Controllers\WithdrawalRequestController;
use App\Mail\ContactUsMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

    Route::middleware(['cors'])->group(function () {


        Route::get('/test-smtp', function() {
            try {
                Mail::raw('This is a test email from Laravel SMTP', function($message) {
                    $message->to('milessabal123@gmail.com') // Replace with your email
                            ->subject('SMTP Test Email');
                });
                return 'Email sent successfully! Check your inbox (and spam folder).';
            } catch (\Exception $e) {
                return 'Error: ' . $e->getMessage();
            }
        });


        Route::post('/contact', function (Request $request) {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email',
                'subject' => 'required|string',
                'phone' => 'required|string|min:7|max:15',
                'message' => 'required|string',
            ]);
        
            $data = $request->all();
        
            Mail::to('support@studioofbooks.org')->send(new ContactUsMail($data));
        
            return response()->json(['message' => 'Email sent successfully'], 200);
        });
        Route::post('/create-checkout-session', [PaymentController::class, 'createCheckoutSession']);
        Route::get('/checkout/success', [PaymentController::class, 'success'])->name('checkout.success');
        Route::get('/checkout/cancel', [PaymentController::class, 'cancel'])->name('checkout.cancel');
       
       
       
        //GLOBAL ROUTES (CAN BE USE BY ADMINS AND AUTHORS)
        //ENTRY POINT
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        //END POINT
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        //FORGOT PASSWORD
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        //RESET PASSWORD
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        //PRESENT ALL BOOKS
        Route::get('/books', [BookController::class, 'index']);
        Route::get('/books/search', [BookController::class, 'search']);
        
        //NOTIFICATIONS ROUTES
        Route::middleware(['auth:sanctum'])->get('/notifications', [NotificationController::class, 'index']);
        Route::middleware(['auth:sanctum'])->post('/notifications/{notificationId}/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::middleware(['auth:sanctum'])->post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);



        //PRESENT SPECIFIC BOOK
        Route::get('/books/{book}', [BookController::class, 'show']); 
        //PRESENT ALL NEWS
        Route::get('/news', action: [NewsController::class, 'index']); 
        //SHOW SPECIFIC NEWS
        Route::get('/news/{news}', [NewsController::class, 'show']); 

        //PRESENT ALL SERVICES
        Route::get('/services', action: [ServiceController::class, 'index']); 
        //SHOW SPECIFIC SERVICE
        Route::get('/services/{service}', [ServiceController::class, 'show']); 


        //PRESENT ALL ADDONS
        Route::get('/addOns', action: [AddOnController::class, 'index']); 
        //SHOW SPECIFIC ADDON
        Route::get('/addOns/{addOn}', [AddOnController::class, 'show']); 


        //GET PROFILE
        Route::middleware(['auth:sanctum'])->get('/user/profile', [AuthController::class, 'getProfile']);
        //EDIT PROFILE
        Route::middleware(['auth:sanctum'])->put('/user/profile', [AuthController::class, 'updateProfile']);


        //PRESENT ALL REPORTS
        Route::middleware(['auth:sanctum'])->get('/reports', action: [ReportController::class, 'index']); // show all reports
        //SHOW SPECIFIC REPORT
        Route::middleware(['auth:sanctum'])->get('/reports/{id}', action: [ReportController::class, 'show']); // show a specific report
        //PRESENT ALL REPORTS
        Route::middleware(['auth:sanctum'])->get('/reports', action: [ReportController::class, 'index']); // show all reports

        //PRESENT ALL REVIEWS
        Route::get('/reviews', action: [ReviewController::class, 'index']); // show all reviews
        Route::post('/reviews', action: [ReviewController::class, 'store']); // post a review

        Route::get('/upcomingBookFairs', [UpcomingBookFairController::class, 'index']); // get all upcoming book fairs
        Route::get('/upcomingBookFairs/{upcomingBookFair}', [UpcomingBookFairController::class, 'show']); // Show an upcoming book fair 


        Route::get('/authorReviews', [AuthorReviewsController::class, 'index']); // get all upcoming book fairs
        Route::get('/authorReviews/{authorReviews}', [AuthorReviewsController::class, 'show']); // Show an author review
        Route::get('/testimonials', [TestimonialController::class, 'index']); // get all testimonials
        Route::get('/testimonials/{testimonial}', [TestimonialController::class, 'show']); // Show a testimonial
        Route::post('/delete-image', [AuthController::class, 'deleteImage']); // when replacing a new image for the author

        Route::get('/banners', [NewsBannerController::class, 'index']); // get all banners
 
        //WEB ADMIN ROUTES
        Route::middleware(['auth:sanctum', 'check.web.admin'])->group(function ()  {
            Route::get('/admin/dashboard', function () {
                return response()->json(['message' => 'Welcome Admin! Your middleware is working.']);
            });

             //AUTHOR MANAGEMENT ROUTES
            Route::get('/admin/authors', action: [AuthorController::class, 'index']); // show all authors
            Route::post('/admin/authors', [AuthorController::class, 'store']); // Create a new author
            Route::get('/admin/authors/{author}', [AuthorController::class, 'show']); // Show a specific author 
            Route::put('/admin/authors/{author}', [AuthorController::class, 'update']); // Update an author 
            Route::delete('/admin/authors/{author}', [AuthorController::class, 'destroy']); // Delete an author 
            Route::patch('admin/authors/{author}/restore', [AuthorController::class, 'restore']); // Reactivate an author

            //NEWS MANAGEMENT ROUTES
            Route::get('/admin/news', action: [NewsController::class, 'index']); // show all news
            Route::post('/admin/news', [NewsController::class, 'store']); // Create a new news
            Route::get('/admin/news/{news}', [NewsController::class, 'show']); // Show a specific news 
            Route::put('/admin/news/{news}', [NewsController::class, 'update']); // Update an news 
            Route::delete('/admin/news/{news}', [NewsController::class, 'destroy']); // Delete an news 
            Route::patch('admin/news/{news}/restore', [NewsController::class, 'restore']); // Reactivate an news

           //REPORT MANAGEMENT ROUTES
           Route::get('/admin/reports', action: [ReportController::class, 'index']); // show all reports
           Route::post('/admin/reports', [ReportController::class, 'store']); // Create a new report
           Route::put('/admin/reports/{reportId}', [ReportController::class, 'update']); // Create a new report
           Route::delete('/admin/reports/{report}', [ReportController::class, 'destroy']); // Delete an news 

           //BOOK MANAGEMENT ROUTES
           Route::post('/admin/books', [BookController::class, 'store']); // Create a new books
           Route::put('/admin/books/{book}', [BookController::class, 'update']); // show a specific book 
           Route::get('/admin/books/{book}', [BookController::class, 'show']); // Update an books 
           Route::delete('/admin/books/{book}', [BookController::class, 'destroy']); // Hide an book
           Route::delete('/admin/books/{book}/permanentlyDelete', [BookController::class, 'permanentlyDelete']); // Delete a book 
           Route::patch('/admin/books/{book}/restore', [BookController::class, 'restore']); // Reactivate a book
           Route::put('/admin/books/{bookId}/set-book-of-the-month', [BookController::class, 'setBookOfTheMonth']);
           Route::put('/admin/books/{bookId}/setIsFeatured', [BookController::class, 'setIsFeatured']);

           //SERVICES MANAGEMENT ROUTES
           Route::post('/admin/services', [ServiceController::class, 'store']); // Create a new service
           Route::put('/admin/services/{service}', [ServiceController::class, 'update']); // show a specific service 
           Route::delete('/admin/services/{service}', [ServiceController::class, 'destroy']); // Delete a service 
    
            //REVIEW MANAGMENT ROUTES    
            Route::patch('/admin/reviews/{review}/approve', [ReviewController::class, 'approve']); // Reactivate a review
            Route::patch('/admin/reviews/{review}/decline', [ReviewController::class, 'decline']); // Decline a review
            Route::delete('/admin/reviews/{review}', [ReviewController::class, 'destroy']); // Delete a review 
        

            //UPCOMING BOOK FAIRS MANAGEMENT ROUTES
           Route::post('/admin/upcomingBookFairs', [UpcomingBookFairController::class, 'store']); // Create a new upcoming book fair
           Route::put('/admin/upcomingBookFairs/{upcomingBookFair}', [UpcomingBookFairController::class, 'update']); // update an upcoming book fair 
           Route::delete('/admin/upcomingBookFairs/{upcomingBookFair}', [UpcomingBookFairController::class, 'destroy']); // Delete an upcoming book fair 


            //UPCOMING BOOK FAIRS MANAGEMENT ROUTES
            Route::post('/admin/authorReviews', [AuthorReviewsController::class, 'store']); // Create a new author review
            Route::put('/admin/authorReviews/{authorReviews}', [AuthorReviewsController::class, 'update']); // update an author review
            Route::delete('/admin/authorReviews/{authorReviews}', [AuthorReviewsController::class, 'destroy']); // Delete an author review
         
           //TESTIMONIALS MANAGEMENT ROUTES
           Route::post('/admin/testimonials', [TestimonialController::class, 'store']); // Create a new testimonial
           Route::put('/admin/testimonials/{testimonial}', [TestimonialController::class, 'update']); // update a testimonial
           Route::delete('/admin/testimonials/{testimonial}', [TestimonialController::class, 'destroy']); // Delete a testimonial

           
           //BANNER MANAGEMENT ROUTES
           Route::post('/admin/banners', [NewsBannerController::class, 'store']); // Create a new banner
           Route::put('/admin/banners/{banner}', [NewsBannerController::class, 'update']); // update a banner
           Route::delete('/admin/banners/{banner}', [NewsBannerController::class, 'destroy']); // Delete a banner


           //ADDONS MANAGEMENT ROUTES
           Route::post('/admin/addOns', [AddOnController::class, 'store']); // Create a new service
           Route::put('/admin/addOns/{addOn}', [AddOnController::class, 'update']); // show a specific service 
           Route::delete('/admin/addOns/{addOn}', [AddOnController::class, 'destroy']); // Delete a service 


           //WITHDRAWAL REQUEST MANAGEMENT ROUTES
           Route::get('/admin/withdrawalRequests', [WithdrawalRequestController::class, 'index']); // get all withdrawal requests
           Route::get('/admin/withdrawalRequests/{withdrawalRequest}', [WithdrawalRequestController::class, 'show']); // Show a withdrawal request
           Route::put('/admin/withdrawalRequests/{withdrawalRequest}', [WithdrawalRequestController::class, 'update']); // show a specific service 
           Route::patch('/admin/withdrawal-requests/{withdrawalRequest}/cancel', [WithdrawalRequestController::class, 'cancel']); // cancel the withdrawal request
           Route::patch('/admin/withdrawal-requests/{withdrawalRequest}/mark-as-processing', [WithdrawalRequestController::class, 'markAsProcessing']);
           Route::patch('/admin/withdrawal-requests/{withdrawalRequest}/mark-as-mailed', [WithdrawalRequestController::class, 'markAsMailed']);
           Route::patch('/admin/withdrawal-requests/{withdrawalRequest}/mark-as-completed', [WithdrawalRequestController::class, 'markAsCompleted']);


        });


        //AUTHOR ROUTES
        Route::middleware(['auth:sanctum', 'check.author'])->group(function ()  {
            Route::get('/author/dashboard', function () {
                return response()->json(['message' => 'Welcome Author! Your middleware is working.']);
            });


            //WITHDRAWAL REQUEST MANAGEMENT ROUTES
            Route::get('/author/my-withdrawal-requests', [WithdrawalRequestController::class, 'authorRequests']); // 

            //WITHDRAWAL REQUEST MANAGEMENT ROUTES
            Route::post('/author/withdrawalRequests', [WithdrawalRequestController::class, 'store']); // Create a new withdrawal request
        
            //TOTAL ACCUMULATED MANAGEMENT ROUTES
            Route::get('/author/totalAccumulatedRoyalty', [TotalAccumulatedRoyaltyController::class, 'index']); // get the totalAccumulatedRoyalty
        });

       

    });