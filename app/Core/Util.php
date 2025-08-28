<?php


namespace App\Core;
use App\Models\WhatsappMessageHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
use Netflie\WhatsAppCloudApi\Message\Template\Component;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use WebSocket\Client;

class Util 
{

    public static function sendMessageWebSocket($data) {
      $options = array_merge([
          'uri' => 'ws://vps.cacimboweb.com',
          'opcode' => 'text',
      ], ['uri:', 'opcode:', 'debug']);

      try {
          // Create client, send and recevie
          $client = new Client($options['uri'], $options);
          $client->send(json_encode($data), $options['opcode']);
          echo "SUCESS: {$client}";
      } catch (\Throwable $e) {
          echo "ERROR: {$e->getMessage()} [{$e->getCode()}]\n";
      }

    }


    public function sendMessageWebSocketApi(Request $request)  {
      self::sendMessageWebSocket($request->all());
    }

    public function dbInfo(Request $request)  {

      if (isset($request->companyID)) {
        //if ($request->version >= 80202415) {
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
            'DB_HOST_SOCKET' => Config::get('database.connections.mysql.host'),
            'DB_DATABASE_SOCKET' => Config::get('database.connections.cacimbosocket.database'),
            'DB_USERNAME_SOCKET' => Config::get('database.connections.cacimbosocket.username'),
            'DB_PASSWORD_SOCKET' => Config::get('database.connections.cacimbosocket.password'),
            'IMG_FTP_PORT' => 21,
            'IMG_FTP_HOST' => env("APP_VPS_HOST"),
            'IMG_FTP_USERNAME' => "admin_ftp_cacimbo",
            'IMG_FTP_PASSWORD' => "casf28Cac!mb0",
            'DOCS_FTP_HOST' => env("DOCS_FTP_HOST"),
            'DOCS_FTP_PORT' => env("DOCS_FTP_PORT"),
            'DOCS_FTP_USERNAME' => env("DOCS_FTP_USERNAME"),
            'DOCS_FTP_PASSWORD' => env("DOCS_FTP_PASSWORD"),
            'PONTOS_HOST' => env("PONTOS_HOST"),
            'PONTOS_DB_PORT' => env("PONTOS_DB_PORT"),
            'PONTOS_DB_DATABASE' => env("PONTOS_DB_DATABASE"),
            'PONTOS_DB_USERNAME' => env("PONTOS_DB_USERNAME"),
            'PONTOS_DB_PASSWORD' => env("PONTOS_DB_PASSWORD"),
            'SOCKECT_URL' => "ws://vps.cacimboweb.com", 
            'SOCKET_PORT' => 8000
        ];
  
        // Retorne as informações do banco de dados como resposta JSON
        return response()->json($dbInfo);
        /*}else{
          abort(403);
        }*/
       
      }else{
        abort(403);
      }
      
    }

    public function handleWithNumber($request){
        $numero = str_replace(" ", "", $request->number);
        $country = strtoupper($request->country);
        $countryCode = $request->country_code;
        // Verifica se o número já tem um indicativo internacional
        if (!str_starts_with($numero, '+')) {
          $numero = $countryCode . $numero;
        }else{
        if (isset($request->country_code)) {
          if(strlen($numero) == 13){
            $newNumber = $numero;
          }else{
            $number = substr(str_replace(" ","",$numero), -9);
            $newNumber = '244'.$number;
          }
          $numero = str_replace(" ","",$newNumber);
        }
      }
        return $numero;
    }

    public function sendMessageWhatsApp(Request $request)
    {
       $mensagem = $request->message_body;

       $numeroT= $this->handleWithNumber($request);

        $whatsapp_cloud_api = new WhatsAppCloudApi([            
            'from_phone_number_id' => $_ENV['WHATSAPP_FROM_PHONE_MUNBER_ID'],
            'access_token' => $_ENV['WHATSAPP_TOKEN'],
        ]);
        $whatsapp_cloud_api->sendTextMessage($numeroT, $mensagem);
        WhatsappMessageHistory::updateOrCreate([
          'to_number' => $numeroT,
          'message_body' => $mensagem,
          'message_type' => 'normal'
        ], [
          'to_number' => $numeroT,
          'message_body' => $mensagem,
          'message_type' => 'normal'
        ]);

    }

    public function sendTemplateMessageWhatsApp(Request $request)
    {
      $mensagem = $request->message_body;
      $image_url = 'https://i.imgur.com/rbe1s7b.jpeg'; 

      $header = [
        [
            'type' => 'image',
            'image' => [
                'link' => $image_url, // URL válida para a imagem
            ],
        ],
    ];
    
    $components = new Component($header);

      $numeroT= $this->handleWithNumber($request);
      // Instantiate the WhatsAppCloudApi super class.108301282118068 103975439223898
      $whatsapp_cloud_api = new WhatsAppCloudApi([            
          'from_phone_number_id' => $_ENV['WHATSAPP_FROM_PHONE_MUNBER_ID'],
          'access_token' => $_ENV['WHATSAPP_TOKEN'],
      ]);
      if(isset($request->template_name)){
        $response = $whatsapp_cloud_api->sendTemplate($numeroT, $request->template_name, 'pt_PT', $components);
      }else{
        $response = $whatsapp_cloud_api->sendTemplate($numeroT, 'documento', 'pt_PT');
      }
      return $response->decodedBody();
     
    }

    public function sendMessageWhatsAppWithAttachment(Request $request)
    {
      /*if($request->country == "AO"){
        $numero = $request->number;
        if(strlen($numero) == 13){
            $newNumber = $numero;
          }else{
            $number = substr(str_replace(" ","",$numero), -9);
            $newNumber = '+244'.$number;
          }
          $numeroT = str_replace(" ","",$newNumber);
        }else{
          $numeroT = $request->number;
        }*/
        $numeroT = $this->handleWithNumber($request);
        $whatsapp_cloud_api = new WhatsAppCloudApi([            
            'from_phone_number_id' => $_ENV['WHATSAPP_FROM_PHONE_MUNBER_ID'],
            'access_token' => $_ENV['WHATSAPP_TOKEN'],
        ]);

        $document_name = $request->doc_name . ".pdf";
        $document_caption = $request->message_body;
        //dd($document_name);

        $document_link = "https://cacimboweb.com/docsERP/".$document_name;
        //dd($document_link);
        $link_id = new LinkID($document_link);
       $respo = $whatsapp_cloud_api->sendDocument($numeroT, $link_id, $document_name, $document_caption);
       $response = $respo->decodedBody();
          WhatsappMessageHistory::updateOrCreate([
          'to_number' => $numeroT,
          'doc_name' => $request->doc_name,
          'message_body' => $document_caption,
          'message_type' => 'attachment'
        ], [
          'to_number' => $numeroT,
          'doc_name' => $request->doc_name,
          'message_body' => $document_caption,
          'message_type' => 'attachment'
        ]);
        
      return $response;
    }
    public function webhooks(Request $request)
    {
      $mode = $request->hub_mode;
      $challenge = $request->hub_challenge;
      $token = $request->hub_verify_token;
    
      return response()->json($mode, 200);
    }
    
}
