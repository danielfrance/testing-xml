<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\Filings\CompanyApplicantController;
use App\Http\Controllers\Filings\CompanyInfoController;
use App\Http\Controllers\Filings\FilingInfoController;
use App\Http\Controllers\Filings\FilingOwnerController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Team\MemberController;
use App\Http\Controllers\Team\TeamController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Str;



// Route::get('/email', function () {
//     return view('emails.invite')->with([
//         'name' => 'John Doe',
//         'type' => 'Beneficial Owner',
//         'inviteURL' => 'https://www.google.com',
//         'actionText' => 'Click here to reset your password',
//         'subject' => 'Reset your password',
//         'title' => 'Reset your password',
//         'expiration' => Carbon\Carbon::now()->addMinutes(60),
//     ]);
// });


// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/', function () {

    return Inertia::render('Website/LandingPage/LandingIndex');
})->name('landing');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/test', function(){
    return Inertia::render('Test');
});



Route::group(['middleware' => ['role:user|administrator|superadministrator', 'auth', 'verified']], function () {

    Route::prefix('filing')->group(function () {

        Route::controller(FilingInfoController::class)->group(function () {
            Route::get('/', 'index')->name('filing.index');
            Route::get('/create', 'create')->name('filing.create');
            Route::get('/{id}', 'show')->name('filing.show');
            Route::post('/{id}/update', 'update')->name('filing.update');

            Route::post('/store', 'store')->name('filing.store');
            Route::post('/{id}/storeAndExit', 'storeAndExit')->name('filing.storeAndExit');
            Route::post('/{id}/updateAndExit', 'updateAndExit')->name('filing.updateAndExit');
        });

        Route::controller(CompanyInfoController::class)->group(function () {
            Route::get('/{id}/company_info', 'create')->name('filing.company_info.create');
            Route::get('/{id}/company_info', 'show')->name('filing.company_info.show');

            Route::post('/{id}/company_info/store', 'store')->name('filing.company_info.store');
            Route::post('/{id}/company_info/update', 'update')->name('filing.company_info.update');
            Route::post('/{id}/company_info/storeAndExit', 'storeAndExit')->name('filing.company_info.storeAndExit');
            Route::post('/{id}/company_info/updateAndExit', 'updateAndExit')->name('filing.company_info.updateAndExit');
        });

        Route::controller(CompanyApplicantController::class)->group(function () {
            Route::get('/{id}/applicants', 'create')->name('filing.applicants.create');
            Route::get('/{id}/applicants', 'show')->name('filing.applicants.show');

            Route::post('/{id}/applicants/store', 'store')->name('filing.applicants.store');
            Route::post('/{id}/applicants/{applicant_id}/update', 'update')->name('filing.applicants.update');
            Route::post('/{id}/applicants/storeAndExit', 'storeAndExit')->name('filing.applicants.storeAndExit');
            Route::post('/{id}/applicants/{applicant_id}/updateAndExit', 'updateAndExit')->name('filing.applicants.updateAndExit');

            Route::get('/{id}/applicants/{applicant_id}/addd', 'addToFiling')->name('filing.applicants.addToFiling');
            Route::get('/{id}/applicants/{applicant_id}/remove', 'removeFromFiling')->name('filing.applicants.removeFromFiling');
        });

        Route::controller(FilingOwnerController::class)->group(function () {
            Route::get('/{id}/owners', 'create')->name('filing.owners.create');
            Route::get('/{id}/owners', 'show')->name('filing.owners.show');

            Route::post('/{id}/owners/store', 'store')->name('filing.owners.store');
            Route::post('/{id}/owners/{owner_id}/update', 'update')->name('filing.owners.update');
            Route::post('/{id}/owners/storeAndExit', 'storeAndExit')->name('filing.owners.storeAndExit');
            Route::post('/{id}/owners/{owner_id}/updateAndExit', 'updateAndExit')->name('filing.owners.updateAndExit');

            Route::get('/{id}/owners/{owner_id}/addd', 'addToFiling')->name('filing.owners.addToFiling');
            Route::get('/{id}/owners/{owner_id}/remove', 'removeFromFiling')->name('filing.owners.removeFromFiling');
        });

        Route::get('/{id}/review', [FilingInfoController::class, 'review'])->name('filing.review');
        Route::get('/{id}/submit', [FilingInfoController::class, 'submitFiling'])->name('filing.submitFiling');

    });


    Route::prefix('owners')->controller(OwnerController::class)->group(function(){
        Route::get('/', 'index')->name('owners.index');
        Route::get('/create', 'create')->name('owners.create');
        Route::get('/{id}/edit', 'edit')->name('owners.edit');

        Route::post('/{id}/update', 'update')->name('owners.update');
        Route::post('/store', 'store')->name('owners.store');
    });

    Route::prefix('applicants')->controller(ApplicantController::class)->group(function () {
        Route::get('/', 'index')->name('applicants.index');
        Route::get('/create', 'create')->name('applicants.create');
        Route::get('/{id}/edit', 'edit')->name('applicants.edit');

        Route::post('/{id}/update', 'update')->name('applicants.update');
        Route::post('/store', 'store')->name('applicants.store');
    });


    // TODO: Create job that will run every 24 hours to check for expired invites and change their status to expired
    Route::post('/invite-owner', [InviteController::class, 'sendInvite'])->name('invite.sendInvite');
    Route::get('/resend-invite/{id}', [InviteController::class, 'resendInvite'])->name('invite.resendInvite');


    //    Team

    Route::prefix('team')->group(function () {

        Route::prefix('members')->controller(MemberController::class)->group(function () {
            Route::get('/', 'index')->name('team.members.index');
            Route::get('/create', 'create')->name('team.members.create');
            Route::get('/{id}/edit', 'edit')->name('team.members.edit');

            Route::post('/{id}/update', 'update')->name('team.members.update');
            Route::post('/store', 'store')->name('team.members.store');

            Route::get('/{id}/password_reset', 'passwordReset')->name('team.members.passwordReset');
        });

        Route::prefix('info')->controller(TeamController::class)->group(function () {
            Route::get('/', 'index')->name('team.info.index');
            Route::get('/create', 'create')->name('team.info.create');
            Route::get('/{id}/edit', 'edit')->name('team.info.edit');

            Route::post('/{id}/update', 'update')->name('team.info.update');
            Route::post('/store', 'store')->name('team.info.store');
        });
    
    });

});

Route::middleware('guest')->group(function () {
    Route::get('{slug}/owner/{token}', [OwnerController::class, 'invitedOwnerEdit'])->name('invite.owner.edit');
    Route::post('invited/owner', [OwnerController::class, 'invitedOwnerStore'])->name('invite.owner.store');


    Route::get('{slug}/applicant/{token}', [ApplicantController::class, 'invitedApplicantEdit'])->name('invite.applicant.edit');
    Route::post('invited/applicant', [ApplicantController::class, 'invitedApplicantStore'])->name('invite.applicant.store');

    Route::get('{slug}/team_member/{token}', [MemberController::class, 'invitedTeamMemberEdit'])->name('invite.teamMember.edit');
    Route::post('invited/team_member', [MemberController::class, 'invitedTeamMemberStore'])->name('invite.teamMember.store');

    Route::get('/expired-token', [InviteController::class, 'showExpiredTokenPage'])->name('invite.expiredToken');
    Route::get('/request-invite/{token}', [InviteController::class, 'requestNewToken'])->name('invite.requestNewToken');

    Route::get('/invite/completed', function () {
        return Inertia::render('Auth/CompletedProfile');
    })->name('invite.completed');

});




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
