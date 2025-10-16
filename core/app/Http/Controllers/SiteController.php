<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Frontend;
use App\Models\Language;
use App\Models\Page;
use App\Models\Subscriber;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    public function index()
    {

        // تغییر (بهینه‌سازی سرعت): کش کردن محتوای صفحه اصلی تا کوئری تکراری به پایگاه داده حذف شود.
        $pageTitle = 'Home';
        $sections = Cache::remember(
            $this->cacheKey('page', activeTemplate(), '/'),
            $this->cacheTtl('page'),
            fn () => Page::where('tempname', activeTemplate())->where('slug', '/')->first()
        );

        // تغییر (کنترل خطا): در صورت نبود صفحه در دیتابیس خطای مناسب برگردانده می‌شود.
        abort_if(!$sections, 404);

        $seoContents = $sections->seo_content;
        $seoImage    = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::home', compact('pageTitle', 'sections', 'seoContents', 'seoImage'));
    }

    public function contact()
    {
        // تغییر (بهینه‌سازی سرعت): استفاده از کش برای صفحه تماس جهت کاهش بار دیتابیس.
        $pageTitle = "Contact Us";
        $user = auth()->user();
        $sections = Cache::remember(
            $this->cacheKey('page', activeTemplate(), 'contact'),
            $this->cacheTtl('page'),
            fn () => Page::where('tempname', activeTemplate())->where('slug', 'contact')->first()
        );

        // تغییر (کنترل خطا): در صورت نبود صفحه در دیتابیس خطای مناسب برگردانده می‌شود.
        abort_if(!$sections, 404);

        $seoContents = $sections->seo_content;
        $seoImage    = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;

        return view('Template::contact', compact('pageTitle', 'user', 'sections', 'seoContents', 'seoImage'));
    }

    public function contactSubmit(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'lastname'  => 'required',
            'email'     => 'required',
            'subject'   => 'required|string|max:255',
            'message'   => 'required',
        ]);

        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $random = getNumber();

        $ticket           = new SupportTicket();
        $ticket->user_id  = auth()->id() ?? 0;
        $ticket->name     = $request->firstname . $request->lastname;
        $ticket->email    = $request->email;
        $ticket->priority = Status::PRIORITY_MEDIUM;


        $ticket->ticket     = $random;
        $ticket->subject    = $request->subject;
        $ticket->last_reply = Carbon::now();
        $ticket->status     = Status::TICKET_OPEN;
        $ticket->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = auth()->user() ? auth()->user()->id : 0;
        $adminNotification->title     = 'A new contact message has been submitted';
        $adminNotification->click_url = urlPath('admin.ticket.view', $ticket->id);
        $adminNotification->save();

        $message                    = new SupportMessage();
        $message->support_ticket_id = $ticket->id;
        $message->message           = $request->message;
        $message->save();

        $notify[] = ['success', 'Ticket created successfully'];

        return to_route('ticket.view', [$ticket->ticket])->withNotify($notify);
    }


    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscribers,email',
        ], [
            'email.unique' => "You have already subscribed"
        ]);

        if ($validator->fails()) {
            return apiResponse('validation_error', 'error', $validator->errors()->all());
        }

        $subscribe        = new Subscriber();
        $subscribe->email = $request->email;
        $subscribe->save();

        return apiResponse('subscribe', 'success', ["Thank you for subscribing us"]);
    }

    public function changeLanguage($lang = null)
    {
        // تغییر (بهینه‌سازی سرعت): کش کردن زبان‌ها برای جلوگیری از کوئری‌های تکراری در تغییر زبان.
        $language = Cache::remember(
            $this->cacheKey('language', $lang),
            $this->cacheTtl('language'),
            fn () => Language::where('code', $lang)->first()
        );
        // تغییر (کنترل خطا): در صورت نبود زبان، به صورت پیش‌فرض انگلیسی انتخاب می‌شود.
        if (!$language) $lang = 'en';
        session()->put('lang', $lang);

        return back();
    }
    public function blogs()
    {
        // تغییر (بهینه‌سازی سرعت): کش کردن لیست بلاگ و محتوای صفحه برای پاسخ‌دهی سریع‌تر.
        $pageTitle = 'Blogs';
        $blogs = Cache::remember(
            $this->cacheKey('blog_list', activeTemplate(), request('page', 1)),
            $this->cacheTtl('blog_list'),
            fn () => Frontend::where('data_keys', 'blog.element')->latest('id')->paginate(getPaginate(18))
        );

        $sections = Cache::remember(
            $this->cacheKey('page', activeTemplate(), 'blog'),
            $this->cacheTtl('page'),
            fn () => Page::where('tempname', activeTemplate())->where('slug', 'blog')->first()
        );

        // تغییر (کنترل خطا): جلوگیری از ادامه پردازش در صورت نبود صفحه بلاگ.
        abort_if(!$sections, 404);

        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? frontendImage('blog', $seoContents->image, getFileSize('seo'), true) : null;
        return view('Template::blogs', compact('pageTitle', 'blogs', 'sections', 'seoContents', 'seoImage'));
    }

    public function pages($slug)
    {
        // تغییر (بهینه‌سازی سرعت): بازیابی صفحه‌های سفارشی از کش برای جلوگیری از کوئری‌های تکراری.
        $page = Cache::remember(
            $this->cacheKey('page', activeTemplate(), $slug),
            $this->cacheTtl('page'),
            fn () => Page::where('tempname', activeTemplate())->where('slug', $slug)->first()
        );

        // تغییر (کنترل خطا): تضمین بازگرداندن 404 اگر صفحه سفارشی پیدا نشود.
        abort_if(!$page, 404);

        $pageTitle   = $page->name;
        $sections    = $page->secs;
        $seoContents = $page->seo_content;
        $seoImage    = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::pages', compact('pageTitle', 'sections', 'seoContents', 'seoImage'));
    }

    public function blogDetails($slug)
    {
        // تغییر (بهینه‌سازی سرعت): کش کردن جزئیات بلاگ و لیست آخرین نوشته‌ها.
        $blog = Cache::remember(
            $this->cacheKey('blog', $slug),
            $this->cacheTtl('blog'),
            fn () => Frontend::where('slug', $slug)->where('data_keys', 'blog.element')->first()
        );

        // تغییر (کنترل خطا): جلوگیری از نمایش صفحه در صورت نبود مطلب بلاگ.
        abort_if(!$blog, 404);

        $latestBlogs = Cache::remember(
            $this->cacheKey('blog_latest', $slug),
            $this->cacheTtl('blog_latest'),
            fn () => Frontend::where('slug', '!=', $slug)->where('data_keys', 'blog.element')->take(10)->latest('id')->get()
        );
        $pageTitle   = strLimit($blog->data_values->title);
        $seoContents = $blog->seo_content;
        if ($seoContents) {
            $seoImage = frontendImage('blog', $seoContents->image, getFileSize('seo'), true);
        } else {
            $seoContents = (object) [
                'title'              => $blog->data_values->title,
                'social_title'       => $blog->data_values->title,
                'description'        => strLimit(strip_tags(@$blog->data_values->description), 300),
                'social_description' => strLimit(strip_tags(@$blog->data_values->description), 300),
            ];
            $seoImage = frontendImage('blog', @$blog->data_values->image);
        }
        return view('Template::blog_details', compact('blog', 'pageTitle', 'seoContents', 'seoImage', 'latestBlogs'));
    }


    public function cookiePolicy()
    {
        // تغییر (بهینه‌سازی سرعت): کش کردن اطلاعات سیاست کوکی برای کاهش تاخیر پاسخ.
        $cookieContent = Cache::remember(
            $this->cacheKey('frontend', 'cookie.data'),
            $this->cacheTtl('frontend'),
            fn () => Frontend::where('data_keys', 'cookie.data')->first()
        );
        // تغییر (کنترل خطا): جلوگیری از خطاهای دسترسی به داده در صورت نبود تنظیمات کوکی.
        abort_if(!$cookieContent, 404);
        abort_if($cookieContent->data_values->status != Status::ENABLE, 404);
        $pageTitle = 'Cookie Policy';
        $cookie = $cookieContent;
        return view('Template::cookie', compact('pageTitle', 'cookie'));
    }


    public function policyPages($slug)
    {
        // تغییر (بهینه‌سازی سرعت): ذخیرهٔ موقتی صفحه‌های سیاست برای جلوگیری از دسترسی‌های سنگین به DB.
        $policy = Cache::remember(
            $this->cacheKey('policy', $slug),
            $this->cacheTtl('policy'),
            fn () => Frontend::where('slug', $slug)->where('data_keys', 'policy_pages.element')->first()
        );

        // تغییر (کنترل خطا): بازگرداندن 404 در صورت حذف یا غیرفعال بودن صفحه سیاست.
        abort_if(!$policy, 404);

        $pageTitle   = $policy->data_values->title;
        $seoContents = $policy->seo_content;
        $seoImage    = @$seoContents->image ? frontendImage('policy_pages', $seoContents->image, getFileSize('seo'), true) : null;
        return view('Template::policy', compact('policy', 'pageTitle', 'seoContents', 'seoImage'));
    }


    public function placeholderImage($size = null)
    {
        if (!$size || !preg_match('/^(\\d{1,4})x(\\d{1,4})$/', $size, $matches)) {
            abort(400, 'Invalid image size format.');
        }

        $config = config('security.placeholder_image');
        $imgWidth = (int) $matches[1];
        $imgHeight = (int) $matches[2];
        $maxDimension = (int) ($config['max_dimension'] ?? 2000);
        $maxPixels = (int) ($config['max_pixels'] ?? 4000000);
        $minDimension = (int) ($config['min_dimension'] ?? 16);

        if ($imgWidth < $minDimension || $imgHeight < $minDimension) {
            abort(422, 'Image dimensions are too small.');
        }

        if ($imgWidth > $maxDimension || $imgHeight > $maxDimension || ($imgWidth * $imgHeight) > $maxPixels) {
            abort(422, 'Requested image dimensions are not allowed.');
        }

        // تغییر (بهینه‌سازی سرعت): ذخیرهٔ نتیجهٔ تصویر جایگزین در کش تا هر اندازه فقط یک بار تولید شود.
        $cacheKey = $this->cacheKey('placeholder_image', $imgWidth . 'x' . $imgHeight);
        $imageData = Cache::remember(
            $cacheKey,
            $this->cacheTtl('placeholder_image'),
            function () use ($imgWidth, $imgHeight) {
                if (!extension_loaded('gd')) {
                    abort(500, 'GD extension is not available.');
                }

                $fontFile = realpath('assets/font/solaimanLipi_bold.ttf');
                if (!$fontFile) {
                    abort(500, 'Placeholder font is not configured.');
                }

                $fontSize = max(9, (int) round(($imgWidth - 50) / 8));
                if ($imgHeight < 100 && $fontSize > 30) {
                    $fontSize = 30;
                }

                $image = imagecreatetruecolor($imgWidth, $imgHeight);
                if (!$image) {
                    abort(500, 'Unable to create image resource.');
                }

                $colorFill = imagecolorallocate($image, 100, 100, 100);
                $bgFill = imagecolorallocate($image, 255, 255, 255);
                imagefill($image, 0, 0, $bgFill);

                $text = $imgWidth . '×' . $imgHeight;
                $textBox = imagettfbbox($fontSize, 0, $fontFile, $text);
                if ($textBox === false) {
                    abort(500, 'Unable to calculate text bounding box.');
                }
                $textWidth = abs($textBox[4] - $textBox[0]);
                $textHeight = abs($textBox[5] - $textBox[1]);
                $textX = (int) (($imgWidth - $textWidth) / 2);
                $textY = (int) (($imgHeight + $textHeight) / 2);

                ob_start();
                $textDrawn = imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
                $imageCreated = imagejpeg($image);
                $imageData = ob_get_clean();
                imagedestroy($image);

                if ($textDrawn === false || $imageCreated === false || $imageData === false) {
                    abort(500, 'Failed to render placeholder image.');
                }

                return $imageData;
            }
        );

        return response($imageData, 200, ['Content-Type' => 'image/jpeg']);
    }

    public function maintenance()
    {
        $pageTitle = 'Maintenance Mode';
        if (gs('maintenance_mode') == Status::DISABLE) {
            return to_route('home');
        }
        // تغییر (بهینه‌سازی سرعت): کش کردن محتوای صفحه نگه‌داری برای واکنش سریع‌تر.
        $maintenance = Cache::remember(
            $this->cacheKey('frontend', 'maintenance.data'),
            $this->cacheTtl('frontend'),
            fn () => Frontend::where('data_keys', 'maintenance.data')->first()
        );
        // تغییر (کنترل خطا): نمایش 404 در صورت نبود دادهٔ صفحه نگه‌داری.
        abort_if(!$maintenance, 404);
        return view('Template::maintenance', compact('pageTitle', 'maintenance'));
    }

    public function cookieAccept()
    {
        Cookie::queue('gdpr_cookie', gs('site_name'), 43200);
    }


    public function pusherAuthentication(Request $request, $socketId, $channelName)
    {
        [$user, $guard] = $this->resolveBroadcastUser();

        abort_if(!$user, 401, 'Authentication required.');

        $prefixes = [
            'web' => 'private-App.Models.User.',
            'agent' => 'private-App.Models.Agent.',
            'merchant' => 'private-App.Models.Merchant.',
            'admin' => 'private-App.Models.Admin.',
        ];

        $prefix = $prefixes[$guard] ?? null;

        if (!$prefix || !str_starts_with($channelName, $prefix)) {
            abort(403, 'Unauthorized channel.');
        }

        $channelUserId = (int) substr($channelName, strlen($prefix));

        if ($channelUserId !== (int) $user->id) {
            abort(403, 'Channel access denied.');
        }

        $general = gs();
        $pusherConfig = $general->pusher_config ?? null;

        if (!$pusherConfig || empty($pusherConfig->app_secret) || empty($pusherConfig->app_key)) {
            abort(503, 'Pusher configuration is unavailable.');
        }

        $payload = $socketId . ':' . $channelName;
        $hash = hash_hmac('sha256', $payload, $pusherConfig->app_secret);

        return response()->json([
            'auth' => $pusherConfig->app_key . ':' . $hash,
        ]);
    }

    private function resolveBroadcastUser(): array
    {
        foreach (['web', 'agent', 'merchant', 'admin'] as $guard) {
            $user = auth()->guard($guard)->user();

            if ($user) {
                return [$user, $guard];
            }
        }

        return [null, null];
    }

    // تغییر (بهینه‌سازی سرعت): متد کمکی برای مدیریت TTL کش به صورت متمرکز.
    private function cacheTtl(string $segment): int
    {
        return (int) config('performance.cache_ttl.' . $segment, 300);
    }

    // تغییر (بهینه‌سازی سرعت): تولید کلیدهای یکتا برای کش تا تداخل داده‌ها رخ ندهد.
    private function cacheKey(string $type, ...$parts): string
    {
        return 'site:' . $type . ':' . md5(json_encode($parts));
    }
}
