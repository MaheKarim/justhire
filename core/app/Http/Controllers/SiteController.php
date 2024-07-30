<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Brand;
use App\Models\Frontend;
use App\Models\Language;
use App\Models\Page;
use App\Models\Subscriber;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\VehicleZone;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;


class SiteController extends Controller
{
    public function index(){
        $reference = @$_GET['reference'];
        if ($reference) {
            session()->put('reference', $reference);
        }

        $pageTitle = 'Home';
        $sections = Page::where('tempname',activeTemplate())->where('slug','/')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::home', compact('pageTitle','sections','seoContents','seoImage'));
    }

    public function pages($slug)
    {
        $page = Page::where('tempname',activeTemplate())->where('slug',$slug)->firstOrFail();
        $pageTitle = $page->name;
        $sections = $page->secs;
        $seoContents = $page->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::pages', compact('pageTitle','sections','seoContents','seoImage'));
    }

    public function contact()
    {
        $pageTitle = "Contact Us";
        $user = auth()->user();
        $sections = Page::where('tempname',activeTemplate())->where('slug','contact')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::contact',compact('pageTitle','user','sections','seoContents','seoImage'));
    }

    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'subject' => 'required|string|max:255',
            'message' => 'required',
        ]);

        $request->session()->regenerateToken();

        if(!verifyCaptcha()){
            $notify[] = ['error','Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $random = getNumber();

        $ticket = new SupportTicket();
        $ticket->user_id = auth()->id() ?? 0;
        $ticket->name = $request->name;
        $ticket->email = $request->email;
        $ticket->priority = Status::PRIORITY_MEDIUM;


        $ticket->ticket = $random;
        $ticket->subject = $request->subject;
        $ticket->last_reply = Carbon::now();
        $ticket->status = Status::TICKET_OPEN;
        $ticket->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = auth()->user() ? auth()->user()->id : 0;
        $adminNotification->title = 'A new contact message has been submitted';
        $adminNotification->click_url = urlPath('admin.ticket.view',$ticket->id);
        $adminNotification->save();

        $message = new SupportMessage();
        $message->support_ticket_id = $ticket->id;
        $message->message = $request->message;
        $message->save();

        $notify[] = ['success', 'Ticket created successfully!'];

        return to_route('ticket.view', [$ticket->ticket])->withNotify($notify);
    }

    public function policyPages($slug)
    {
        $policy = Frontend::where('slug',$slug)->where('data_keys','policy_pages.element')->firstOrFail();
        $pageTitle = $policy->data_values->title;
        $seoContents = $policy->seo_content;
        $seoImage = @$seoContents->image ? frontendImage('policy_pages',$seoContents->image,getFileSize('seo'),true) : null;
        return view('Template::policy',compact('policy','pageTitle','seoContents','seoImage'));
    }

    public function changeLanguage($lang = null)
    {
        $language = Language::where('code', $lang)->first();
        if (!$language) $lang = 'en';
        session()->put('lang', $lang);
        return back();
    }

    public function blog()
    {
        $blogs     = Frontend::where('tempname', activeTemplateName())->where('data_keys', 'blog.element')->latest()->paginate(getPaginate(12));
        $pageTitle = 'Blog';
        $page      = Page::where('tempname', activeTemplate())->where('slug', 'blog')->first();
        $sections  = $page->secs;
        return view('Template::blog', compact('blogs', 'pageTitle', 'sections'));
    }

    public function blogDetails($slug)
    {
        $blog = Frontend::where('slug',$slug)->where('data_keys','blog.element')->firstOrFail();
        $latestBlogs = Frontend::where('$slug', '!=', $slug)->where('data_keys', 'blog.element')->orderBy('id', 'desc')->limit(5)->get();
        $pageTitle = $blog->data_values->title;
        $seoContents = $blog->seo_content;
        $seoImage = @$seoContents->image ? frontendImage('blog',$seoContents->image,getFileSize('seo'),true) : null;

        return view('Template::blog_details', compact('blog','pageTitle','seoContents','seoImage', 'latestBlogs'));
    }

    public function cookieAccept(){
        Cookie::queue('gdpr_cookie',gs('site_name') , 43200);
    }

    public function cookiePolicy(){
        $cookieContent = Frontend::where('data_keys','cookie.data')->first();
        abort_if($cookieContent->data_values->status != Status::ENABLE,404);
        $pageTitle = 'Cookie Policy';
        $cookie = Frontend::where('data_keys','cookie.data')->first();
        return view('Template::cookie',compact('pageTitle','cookie'));
    }

    public function placeholderImage($size = null){
        $imgWidth = explode('x',$size)[0];
        $imgHeight = explode('x',$size)[1];
        $text = $imgWidth . '×' . $imgHeight;
        $fontFile = realpath('assets/font/solaimanLipi_bold.ttf');
        $fontSize = round(($imgWidth - 50) / 8);
        if ($fontSize <= 9) {
            $fontSize = 9;
        }
        if($imgHeight < 100 && $fontSize > 30){
            $fontSize = 30;
        }

        $image     = imagecreatetruecolor($imgWidth, $imgHeight);
        $colorFill = imagecolorallocate($image, 100, 100, 100);
        $bgFill    = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgFill);
        $textBox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth  = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $textX      = ($imgWidth - $textWidth) / 2;
        $textY      = ($imgHeight + $textHeight) / 2;
        header('Content-Type: image/jpeg');
        imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
        imagejpeg($image);
        imagedestroy($image);
    }

    public function maintenance()
    {
        $pageTitle = 'Maintenance Mode';
        if(gs('maintenance_mode') == Status::DISABLE){
            return to_route('home');
        }
        $maintenance = Frontend::where('data_keys','maintenance.data')->first();
        return view('Template::maintenance',compact('pageTitle','maintenance'));
    }

    public function subscribe(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $emailExist = Subscriber::where('email', $request->email)->first();

        if ($emailExist) {
            return response()->json(['error' => 'Already subscribed']);
        }

        $subscribe        = new Subscriber();
        $subscribe->email = $request->email;
        $subscribe->save();

        return response()->json(['success' => 'Subscribed successfully']);
    }

    public function vehicles(Request $request, $slug = null) {
        $pageTitle      = 'Vehicles';
        $vehicleClasses = null;
        $vehicleType    = null;
        if ($slug) {
            $vehicleType = VehicleType::active()->withWhereHas('vehicleClass', function ($class) {
                $class->active();
            })->where('slug', $slug)->first();
            if (!$vehicleType) {
                $notify[] = ['error', 'Vehicle type not found'];
                return to_route('home')->withNotify($notify);
            }
            $vehicleClasses = $vehicleType->vehicleClass;
        }

        if ($vehicleType && !$vehicleType->manage_class) {
            $vehicles = $this->filterVehicles($vehicleType, null);
        } else {
            $vehicles = $this->filterVehicles(null, $vehicleClasses);
        }

        $brands = Brand::active()->orderBy('name')->get(['id', 'name']);
        $zones  = Zone::active()->orderBy('name')->get();
        return view('Template::vehicle.index', compact('pageTitle', 'vehicles', 'brands', 'zones', 'vehicleClasses', 'slug', 'vehicleType'));
    }

    public function vehicleDetail($id) {
        $pageTitle = 'Vehicle Detail';
        $vehicle   = Vehicle::available()->with(['rental' => function ($query) {
            $query->activeToday();
        }])->with('reviewData.user')->findOrFail($id);
        $zones = $vehicle->zones()->with('locations')->get();
        return view('Template::vehicle.detail', compact('pageTitle', 'vehicle', 'zones'));
    }

    public function filterVehicles($vehicleType = null, $vehicleClass = null) {

        $vehicles = Vehicle::available();
        if ($vehicleType) {
            $vehicles->where('vehicle_type_id', $vehicleType->id);
        }

        if ($vehicleClass) {
            $vehicles->whereIn('vehicle_class_id', $vehicleClass->pluck('id')->toArray());
        }

        $request = request();

        if ($request->vehicle_type_id) {
            $vehicles->where('vehicle_type_id', $request->vehicle_type_id);
        }

        if ($request->pick_up_zone_id) {
            $pickZoneId = User::where('zone_id', $request->pick_up_zone_id)->pluck('id')->toArray();
            $vehicles->whereIn('user_id', $pickZoneId);
        }

        if ($request->date) {
            $date      = explode('-', $request->date);
            $startDate = Carbon::parse(trim($date[0]))->format('Y-m-d');
            $endDate   = @$date[1] ? Carbon::parse(trim(@$date[1]))->format('Y-m-d') : $startDate;
            $vehicles->whereDoesntHave('rental', function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate)
                        ->where('end_date', '>=', $startDate);
                });
            });
        }

        if ($request->drop_off_zone_id) {
            $dropZoneId = VehicleZone::where('zone_id', $request->drop_off_zone_id)->pluck('vehicle_id')->toArray();
            $vehicles->whereIn('id', $dropZoneId);
        }

        if ($request->price) {
            list($column, $value) = explode('_', $request->price);
            $vehicles->orderBy($column, $value);
        }

        return $vehicles = $vehicles->filter(['fuel_type', 'transmission_type', 'vehicle_class_id', 'brand_id'])->with('user.zone')->orderBy('id', 'desc')->paginate(getPaginate(18));
    }

}
