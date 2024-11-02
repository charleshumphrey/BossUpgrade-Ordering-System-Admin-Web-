<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Str;
use Kreait\Firebase\Storage as FirebaseStorage;

class PromotionsController extends Controller
{
    protected $firebaseService;
    protected $firebaseStorage;
    protected $promotionsTable = 'promotions';


    public function __construct(FirebaseService $firebaseService, FirebaseStorage $firebaseStorage)
    {
        $this->firebaseService = $firebaseService;
        $this->firebaseStorage = $firebaseStorage;
    }


    public function index()
    {
        $promotions = $this->firebaseService->getPromotions();

        return view('promotions', compact('promotions'));
    }

    public function store(Request $request)
    {
        // Validate the uploaded images
        $validatedData = $request->validate([
            'promotional_images' => 'required|array', // Ensure it's an array
            'promotional_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Each image validation
        ]);

        // Array to hold the promotional image URLs
        $promoImageUrls = [];

        // Check if promotional images are provided and store them in Firebase Storage
        if ($request->hasFile('promotional_images')) {
            foreach ($request->file('promotional_images') as $promoImage) {
                $promoImageName = Str::random(20) . '.' . $promoImage->getClientOriginalExtension();
                $promoFirebaseStoragePath = 'promotions/' . $promoImageName;

                // Upload the image to Firebase Storage
                $uploadedPromoFile = $this->firebaseStorage->getBucket()->upload(
                    file_get_contents($promoImage->getRealPath()),
                    [
                        'name' => $promoFirebaseStoragePath,
                        'predefinedAcl' => 'publicRead', // Make the file publicly accessible
                    ]
                );

                // Construct the public URL for the image
                $promoImageUrl = 'https://storage.googleapis.com/' . $this->firebaseStorage->getBucket()->name() . '/' . $promoFirebaseStoragePath;

                // Add the image URL to the array
                $promoImageUrls[] = $promoImageUrl;
            }
        }

        // Get a reference to the Firebase Realtime Database
        $database = $this->firebaseService->getDatabase();
        $promotionsRef = $database->getReference('promotions');

        // Retrieve current promotions (if any)
        $currentPromotions = $promotionsRef->getValue() ?: [];

        // Calculate the next available index (to continue from where the last key left off)
        $nextIndex = count($currentPromotions);

        // Loop through the promotional image URLs and add them to the database under a sequential numeric key
        foreach ($promoImageUrls as $url) {
            // Store each URL under a new numeric key
            $promotionsRef->getChild($nextIndex)->set($url);
            $nextIndex++; // Increment the index for the next image
        }

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Promotional images added successfully.');
    }



    public function destroy($key)
    {
        $database = $this->firebaseService->getDatabase();
        $promotionsRef = $database->getReference('promotions');

        if ($promotionsRef->getChild($key)->getValue()) {

            $promotionsRef->getChild($key)->remove();

            $promotions = $promotionsRef->getValue();

            if (empty($promotions)) {
                return redirect()->back()->with('success', 'Promotion image deleted successfully.');
            }

            $newPromotions = [];

            foreach ($promotions as $index => $promotion) {
                if ($index != $key) {
                    $newPromotions[] = $promotion;
                }
            }

            $promotionsRef->set($newPromotions);

            return redirect()->back()->with('success', 'Promotion image deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Promotion not found.');
        }
    }
}
