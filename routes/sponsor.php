use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SponsorController;

Route::resource('sponsors', SponsorController::class);