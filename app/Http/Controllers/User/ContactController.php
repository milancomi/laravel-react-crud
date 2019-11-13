<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contacts;
use Validator;
use Illuminate\Routing\UrlGenerator;

class ContactController extends Controller
{
protected $contacts;
protected $base_url;
public function __construct(UrlGenerator $urlGenerator)
{
    $this->middleware("auth:users");
    $this->contacts = new Contacts;
    $this->base_url = $urlGenerator->to("/");
}

//this function/end-point is to create new contact spec for user /
public function addContacts(Request $request)
{
    $validator = Validator::make($request->all(),
    [
        "token"=>"required",
        "first_name"=>"required|string",
        "phonenumber"=>"required|string"
    ]);

    if($validator->fails())
    {
        return response()->json([
            "success"=>false,
            "message"=>$validator->messages()->toArray()
        ],500);
    }

    $profile_picture = $request->profile_image;
    $file_name = "";
    if($profile_picture ==null){
        $file_name = "default-avatar.png";
    }else{
        $generate_name = uniqid()."_".time().date("Ymd")."_IMG";
        $base64Image = $profile_picture;

        $fileBin= file_get_contents($base64Image);
        $mimetype = mime_content_type($fileBin);
        if("image/png"==$mimetype)
        {
            $file_name = $generate_name.".png";
        }
        else if("image/jpeg"==$mimetype)
        {
            $file_name = $generate_name.".jpeg";
        }
        else if("image/jpg"==$mimetype)
        {
            $file_name = $generate_name.".jpg";
        }
        else{
            return response()->json([
                "success"=>false,
                "message"=>"only png, jpg and jpeg file accepted"
            ],500);
        }
    }


    $user_token = $request->token;
    $user = auth("users")->authenticate($user_token);
    $user_id = $user->id;

    $this->contacts->user_id = $user_id;
    $this->contacts->phonenumber = $request->phonenumber;
    $this->contacts->first_name = $request->first_name;
    $this->contacts->last_name = $request->last_name;
    $this->contacts->email = $request->email;
    $this->contacts->image_file = $request->image_file;

    $this->contact->save();
    if($profile_picture == null)
    {

    }else{
        file_put_contents("./profile_images/".$file_name,$fileBin);
    }

    return response()->json([
        "success"=>true,
        "message"=>"Contact saved successfully !"
    ],200);

}

//getting contact specific to a particular user
public function getPaginatedData($token,$pagination=null)
{
    $file_directory = $this->base_url."profile_images";
    $user = auth("users")->authenticate($token);

    $user_id = $user->id;
    if($pagination==null || $pagination==""){

        $contacts = $this->contacts->where("user_id",$user_id)->orderBy("id","DESC")->get()->toArray();
        return response()->json([
            "success"=>true,
            "data"=>$contacts,
            "file_directory"=>$file_directory
        ],200);
    }

    $contacts_paginated = $this->contacts->where("user_id",$user_id)->orderBy("id","DESC")->paginate($pagination);

    return response()->json([
        "success"=>true,
        "data"=>$contacts_paginated,
        "file_directory"=>$file_directory
    ],200);
}

public function editSingleData(Request $request,$id)
{

    $validator = Validator::make($request->all(),
    [
        "first_name"=>"required|string",
        "phonenumber"=>"required|string"
    ]);

    if($validator->fails())
    {
        return response()->json([
            "success"=>false,
            "message"=>$validator->messages()->toArray()
        ],500);
    }

    $findData = $this->contacts::find($id);
    if(!$findData)
    {
        return response()->json([
            "success"=>false,
            "message"=>"Id is not valid"
        ],500);
        }

        $getFile = $findData->image_file;
        $getFile=="default-avatar.png";
        $profile_picture = $request->profile_image;
        $file_name = "";

        if($profile_picture ==null){
            $file_name = "default-avatar.png";
        }else {
            $generate_name = uniqid()."_".time().date("Ymd")."_IMG";
            $base64Image = $profile_picture;

            $fileBin= file_get_contents($base64Image);
            $mimetype = mime_content_type($fileBin);
            if("image/png"==$mimetype)
            {
                $file_name = $generate_name.".png";
            }
            else if("image/jpeg"==$mimetype)
            {
                $file_name = $generate_name.".jpeg";
            }
            else if("image/jpg"==$mimetype)
            {
                $file_name = $generate_name.".jpg";
            }
            else{
                return response()->json([
                    "success"=>false,
                    "message"=>"only png, jpg and jpeg file accepted"
                ],500);
            }

            $findData->first_name = $request->first_name;
            $findData->phonenumber = $request->phonenumber;
            $findData->image_file = $file_name;
            $findData->last_name = $request->last_name;
            $findData->email = $request->email;
            $findData->save();


    if($profile_picture == null)
    {

    }else{
        file_put_contents("./profile_images/".$file_name,$fileBin);
    }
    return response()->json([
        "success"=>true,
        "message"=>"Contact updated successfully !"
    ],200);
        }
        return response()->json([
            "success"=>true,
            "message"=>"Contact updated successfully !",
            "data"=>$findData
        ],200);
}

public function deleteContacts($id)
{

    $findData = $this->contacts::find($id);
    if(!$findData)
    {

    return response()->json([
        "success"=>true,
        "message"=>"Contact with this id doesnt exist !"
    ],500);
    }


    $getFile = $findData->image_file;
    if($findData->delete())
    {
        // $getFile == "default-avatar.png" ? :unlink("./profile_image".$getFile);

        return response()->json([
            "success"=>true,
            "message"=>"Contact deleted successfully !"
        ],200);
            }

    }

    public function getSingleData($id)
    {

        $findData = $this->contacts::find($id);
        $file_directory = $this->base_url."/profile_images";
        if(!$findData)
        {

        return response()->json([
            "success"=>true,
            "message"=>"Contact with this id doesnt exist !"
        ],500);
        }

        return response()->json([
            "success"=>true,
            "data"=>$findData,
            "file_directory" =>$file_directory
        ],200);
        }


        public function searchData($search,$token,$pagination=null)
        {

            $file_directory = $this->base_url."/profile_images";
            $user = auth("users")->authenticate($token);
            $user_id = $user->id;

            if($pagination==null || $pagination=="")
            {

                $non_paginated_search_query = $this->contacts::where("user_id",$user_id)->
                where(function($query) use ($search){
                    $query-where("first_name","LIKE","%$search%")->orWhere("last_name","LIKE","%$search%")
                    ->orWhere("email","LIKE","%$search%")
                    ->orWhere("phonenumber","LIKE","%$search%");
                })->orderBy("id","DESC")->get()->toArray();

                return response()->json([
                    "success"=>true,
                    "data"=>$non_paginated_search_query,
                    "file_directory" =>$file_directory
                ],200);
            }
            $paginated_search_query = Contacts::where("user_id",$user_id)->
            where(function($query) use ($search){
                $query->where("first_name","LIKE","%$search%")->orWhere("last_name","LIKE","%$search%");
                // ->orWhere("email","LIKE","%$search%")
                // ->orWhere("phonenumber","LIKE","%$search%");
            })->orderBy("id","DESC")->paginate($pagination);
            return response()->json([
                "success"=>true,
                "data"=>$paginated_search_query,
                "file_directory" =>$file_directory
            ],200);

        }

    }






