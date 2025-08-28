<?php

use App\Core\Util;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ConfigStatusController;
use App\Http\Controllers\ConfigStoresPaymentsMechanismController;
use App\Http\Controllers\DocBankAccountController;
use App\Http\Controllers\DocEmpresaController;
use App\Http\Controllers\DocEntidadeController;
use App\Http\Controllers\DocLinhaController;
use App\Http\Controllers\DocPaymentController;
use App\Http\Controllers\DocPaymentsMechanismController;
use App\Http\Controllers\DocTypeController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\ErrorLogController;
use App\Http\Controllers\GenericController;
use App\Http\Controllers\OnlinePaymentController;
use App\Http\Controllers\OnlineStoreController;
use App\Http\Controllers\RhAlteracaoMensalController;
use App\Http\Controllers\RhFuncionarioController;
use App\Http\Controllers\RhRubricaController;
use App\Http\Controllers\SocketInfoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserEmpresaController;

use App\Http\Controllers\WhatsappMessageHistoryController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/*Route::middleware(['auth:sanctum'])->group(function () {
  return;  
});*/


//Route::resource('docs', DocsController::class);
Route::resource('docs_linhas', DocLinhaController::class);
Route::resource('error-logs', ErrorLogController::class);
Route::resource('config-status', ConfigStatusController::class);
Route::resource('online-stores', OnlineStoreController::class);
//Route::resource('socket-info', SocketInfoController::class);
Route::get('socket-info', [DocEmpresaController::class, 'showClientesOnline']);
Route::resource('config-payments-mechanism', ConfigStoresPaymentsMechanismController::class);
//Route::post('online-stores/{onlineStore}', [OnlineStoreController::class, "update"])->name('update.online-store');
//Route::resource('docs_entidades', DocEmpresaController::class);
//Route::get('users/{user_id}/empresas', [DocEmpresaController::class, 'getEmpresasByUserId'])->name('users.empresas');
//Route::resource('docs_empresas', DocEntidadeController::class);
Route::get('rh-funcionarios/{nif}', [RhFuncionarioController::class, 'index']);
Route::get('docs_empresas/{docEmpresa}', [DocEmpresaController::class, 'show']);
Route::get('rh-rubricas/{nif}', [RhRubricaController::class, 'index']);
Route::get('rh-alteracoes-mensais/{nif}', [RhAlteracaoMensalController::class,'index']);
Route::post('rh-alteracoes-mensais', [RhAlteracaoMensalController::class,'store']);
Route::get('rh-alteracoes-mensais/{nif}/funcionario/{funcionario_id}', [RhAlteracaoMensalController::class,'getAllRhAlteracoesMensaisByFuncionarioId']);
Route::post('store-all', [GenericController::class, 'storeAllDataInDataBase']);
Route::get('get-all', [GenericController::class, 'getAllDataInDataBase']);
Route::get('docs/{nif}', [DocsController::class,'documents']);
Route::get('docs_entidades/{nif}', [DocEntidadeController::class,'getAllDocEntidadesByCompanyID']);
Route::post('docs_entidades', [DocEntidadeController::class,'store']);
Route::post('docs', [DocsController::class,'store']);
Route::post('docs/cart/order', [DocsController::class,'purchaseOrderOnline']);
Route::put('send-push-token', [UserController::class, "sendPushToken"]);

Route::get('docs/onlines/encomendas/{nif}', [DocsController::class,'getDocsDraftsOrderDetailsWithLines']);
Route::get('docs/sales/{nif}', [DocsController::class,'documentsSalesDetails']);
Route::delete('docs/drafts/delete/{invoiceId}', [DocsController::class,'deleteDocDraft']);
Route::put('docs/drafts/update/{invoiceId}', [DocsController::class,'updateDocDraft']);
Route::get('docs/drafts/{nif}', [DocsController::class,'documentsDraftsDetails']);
Route::get('docs/current-account/{nif}/customer/{customerId}', [DocsController::class,'documentsCustomerCurrentAccount']);
Route::get('docs/current-account/{nif}/customer/{customerId}/details', [DocsController::class,'documentsCustomerCurrentAccountDetails']);
Route::get('docs/balances/{nif}', [DocsController::class,'documentsCustomerBalance']);
Route::get('v2/docs/balances/{nif}', [DocsController::class,'documentsCustomerBalanceV2']);
Route::get('docs/inventory/{nif}', [DocsController::class,'documentsInventoryDetails']);
Route::get('docs/purchases/{nif}', [DocsController::class,'documentsPurchasesDetails']);
Route::get('docs/workings/{nif}', [DocsController::class,'documentsWorkingsDetails']);
Route::get('docs/sales-payments/{nif}', [DocsController::class,'documentsSalesWithPaymentsDetails']);
Route::post('token', [LoginController::class,'requestToken']);
Route::get('users/{user}/empresas', [LoginController::class,'getEmpresasByLoggedUserId']);
Route::get('users/{user}/all-empresas', [UserController::class,'getEmpresas']);
Route::get('docs/analytics/{nif}', [DocsController::class,'analyticsForCharts']);
Route::post('send-message-whatsapp', [Util::class, 'sendMessageWhatsApp'])->name('send-message-whatsapp');
Route::put('whatsapp-message-histories', [WhatsappMessageHistoryController::class, 'updateMessageStatus'])->name('whatsapp-message-histories');
Route::post('whatsapp-message-histories', [WhatsappMessageHistoryController::class, 'store'])->name('whatsapp-message-histories.store');
Route::get('whatsapp-message-histories/{number}', [WhatsappMessageHistoryController::class, 'index'])->name('whatsapp-message-histories.index');
Route::post('send-template-message-whatsapp', [Util::class, 'sendTemplateMessageWhatsApp'])->name('send-template-message-whatsapp');
Route::post('send-message-whatsapp-with-attachment', [Util::class, 'sendMessageWhatsAppWithAttachment'])->name('send-message-whatsapp-attachment');
Route::get('webhooks', [Util::class, 'webhooks'])->name('webhooks');

Route::get('docs/types/{nif}', [DocTypeController::class,'allDocTypeByCompanyID']);
Route::get('doc-types', [DocTypeController::class,'allDocTypes']);

Route::get('doc-types/{id}', [DocTypeController::class,'getDocType']);
Route::get('docs/last-status/{id}', [DocsController::class,'getLastDocStatusCode']);

Route::get('docs/bank-accounts/{nif}', [DocBankAccountController::class,'allDocBankAcountsByCompanyID']);
Route::get('payments/mechanism/{nif}', [DocPaymentsMechanismController::class,'allPaymentsMechanismsByCompanyID']);
Route::get('doc-entidade/{docEntidade}', [DocEntidadeController::class,'changeWhatsappStatus']);
Route::get("docs_empresas/{companyId}/users", [DocEmpresaController::class, 'getUsersByCompanyId']);
Route::get("docs_empresas", [DocEmpresaController::class, 'getAllDocsEmpresas']);
Route::get("docs_empresas/all-by-nif/{nif}", [DocEmpresaController::class, 'getAllDocsEmpresasByNif']);
Route::get("online-stores/all-by-nif/licenca/modulo/{modulo_id}", [OnlineStoreController::class, 'getOnlineStoresByNIFs']);

Route::put('users/{user}/change-last-company-used', [UserEmpresaController::class, 'changeLastCompanyIDUsed']);
Route::get('docs_empresas/nif/{nif}', [UserEmpresaController::class, 'getAllDocEmpresasByNif']);
Route::post('docs_empresas/associate-user', [UserEmpresaController::class, 'associateUserToCompany']);
Route::post('online-stores/encomenda/payment', [OnlinePaymentController::class, "payForOnlineDoc"]);

Route::get('users', function () {
    return UserResource::collection(App\Models\User::all());
});
/*
Route::get('db-info', function () {
     $dbInfo = [
            'DB_CONNECTION' => Config::get('database.default'),
            'DB_HOST' => Config::get('database.connections.mysql.host'),
            'DB_PORT' => Config::get('database.connections.mysql.port'),
            'DB_DATABASE' => Config::get('database.connections.mysql.database'),
            'DB_USERNAME' => Config::get('database.connections.mysql.username'),
            'DB_PASSWORD' => Config::get('database.connections.mysql.password'),
            'DB_DATABASE_LINHAS' => Config::get('database.connections.cacimbodocs.database'),
            'DB_USERNAME_LINHAS' => Config::get('database.connections.cacimbodocs.username'),
            'DB_PASSWORD_LINHAS' => Config::get('database.connections.cacimbodocs.password'),
        ];

        // Retorne as informações do banco de dados como resposta JSON
        return response()->json($dbInfo);
});//->middleware('auth:sanctum');*/
Route::get('db-info', [Util::class, 'dbInfo']);

