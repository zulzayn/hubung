<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function __construct() {
        $this->middleware('auth')->except('index' , 'store');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
  
        return view('chat.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text_message' 	=> 'required',
            'id_user' 		=> 'required',
            'id_user_other' => 'required',
        ]);
        
        if($validator->fails()){
            $data = [
                'status' => 'error', 
                'type' => 'Validation Error',
                'message' => 'Validation error, please check back your input.' ,
                'error_list' => $validator->messages() ,
            ];
            return json_encode($data);
        }

        $add = New Chat;
        $add->id_user = $request->id_user;
        $add->id_user_other = $request->id_user_other;
        $add->text = $request->text_message;
        $add->parent = 0;
        $add->status_user = 'R';
        if($request->id_user == $request->id_user_other){
            $add->status_user_other = 'R';
        }
        else{
            $add->status_user_other = 'S';
        }
        $add->created_at = date('Y-m-d H:i:s');
        $add->save();
    
        $chat = Chat::find($add->id);
       
        if($add->id_user == $add->id_user_other){
            $chat->unread_count_self = 0;
            $chat->unread_count_other = 0;
        }
        else{
            $unread_count_self = Chat::where('id_user' , $add->id_user)
                                ->where('id_user_other' , $add->id_user_other)
                                ->where('status_user_other' , 'S')
                                ->get();
            $chat->unread_count_self = count($unread_count_self);

            $unread_count_other = Chat::where('id_user' , $add->id_user)
                                ->where('id_user_other' , $add->id_user_other)
                                ->where('status_user' , 'S')
                                ->get();
            $chat->unread_count_other = count($unread_count_other);
        }
       

        $data = [
            'status' => 'success', 
            'message' => 'Successfully send chat.',
            'data' => $chat,
        ];
        return json_encode($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
 
        if($request->id_user && $request->id_user_other){
            $chats = Chat::where('id_user' , $request->id_user)
            ->where('id_user_other' , $request->id_user_other)
            ->where('status_user_other' , 'S')
            ->get();

            foreach ($chats  as $key => $chat) {
                $chat->status_user_other = "R";
                $chat->save();
            }
        }

        return view('chat.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function chatcontent($id){

        $user = auth()->user();
        $other_user = User::find($id);
        $chats = Chat::where('id_user' , auth()->user()->id)
        ->where('id_user_other' , $id)
        ->orderBy('created_at')
        ->orWhere('id_user' , $id)
        ->where('id_user_other' , auth()->user()->id)
        ->orderBy('created_at')
        ->get(); 

        $data = [
            'status' => 'success', 
            'message' => 'Successfully fetch chat content data.',
            'user' => $user, 
            'other_user' => $other_user, 
            'chat' => $chats,
        ];
        return json_encode($data);
    }

    public function chatpreview(){

        $chatgroups = Chat::where('id_user' , auth()->user()->id)
        ->orWhere('id_user_other' , auth()->user()->id)
        ->orderBy('created_at' , 'DESC') 
        ->get() 
        ->groupBy(function($data) {

            if($data->id_user > $data->id_user_other)
            {
                return $data->id_user.$data->id_user_other;
            }
            elseif($data->id_user < $data->id_user_other)
            {
                return $data->id_user_other.$data->id_user;
            }
            else
            {
                return $data->id_user.$data->id_user_other;
            }
            
        });

        
        $dataArrs = array();

        $lasttext = '';
        $lastcreated = '';

        foreach ($chatgroups as $keyA => $user) {

            $unread_count = 0;

            foreach ($user as $keyB => $chat) {

                if($chat->id_user == $chat->id_user_other){
                    if($chat->status_user == "S" || $chat->status_user_other == "S"){
                        $unread_count++;
                    }
                }

                if($chat->id_user == auth()->user()->id){
                    if($chat->status_user == "S"){
                        $unread_count++;
                    }
                }

                if($chat->id_user_other == auth()->user()->id){
                    if($chat->status_user_other == "S"){
                        $unread_count++;
                    }
                }
                  
                if($keyB == 0){

                    if($chat->id_user == $chat->id_user_other)
                    {
                        $user_other = User::find($chat->id_user);
                        $user = User::find($chat->id_user_other);
                        $lasttext = $chat->text;
                        $lastcreated = $chat->created_at;
                    }
                    else
                    {
                        
                        if($chat->id_user == auth()->user()->id)
                        {
                            $user_other = User::find($chat->id_user_other);
                            $user = User::find($chat->id_user);
                            $lasttext = $chat->text;
                            $lastcreated = $chat->created_at;
                        }
                        else
                        {
                            $user_other = User::find($chat->id_user);
                            $user = User::find($chat->id_user_other);
                            $lasttext = $chat->text;
                            $lastcreated = $chat->created_at;
                        }
                      
                    }
                    
                }

            }

            $dataArr["user"] 	        = $user_other;
            $dataArr["user_other"] 	    = $user;
            $dataArr["last_text"] 	    = $lasttext;
            $dataArr["last_created"] 	= $lastcreated;
            $dataArr["unread_count"] 	= $unread_count;
            array_push($dataArrs, $dataArr);

        } 


        usort($dataArrs, function($a, $b) {
            return $a['last_created'] < $b['last_created'];
        });



        $data = [
            'status' => 'success', 
            'message' => 'Successfully fetch chat preview data.',
            'chat' => $dataArrs,
        ];
        return json_encode($data);
    }

    public function updatechatstatus($id_user, $id_user_other){

        $chats = Chat::where('id_user' , $id_user_other)
                    ->where('id_user_other' , $id_user)
                    ->where('status_user_other' , 'S')
                    ->get();

        foreach ($chats  as $key => $chat) {
            $chat->status_user_other = "R";
            $chat->save();
        }

        $data = [
            'status' => 'success', 
            'message' => 'Successfully update chat status.',
        ];
        return json_encode($data);

    }

}
