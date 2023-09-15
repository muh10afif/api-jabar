<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
/**
 * @OA\Swagger(
 *     basePath="/",
 *     schemes={"https://10.59.105.208/api-jabar/api/v1/"},
 *     @OA\Info(
 *         version="1.0.0",
 *         title="API Jabar documentation"
 *     )
 * )
 */

 /**
*  @OA\Server(
*      url="https://10.59.105.208/api-jabar/api/v1/",
*      description="Host"
*  )
*
*/

/**
* @OA\SecurityScheme(
*   securityScheme="bearerAuth",
*   type="http",
*   scheme="bearer",
*   in="header",
*   name="Authorization",
* )
*/

/**
* @OA\Post(
*     path="/register",
*     summary="Register new user",
*     tags={"User"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="firstname",
*           type="string"
*         ),
*         @OA\Property(
*           property="lastname",
*           type="string",
*         ),
*         @OA\Property(
*           property="email",
*           type="string",
*         ),
*         @OA\Property(
*           property="password",
*           type="string",
*         ),
*       ),
*     ),
*     @OA\MediaType(
*       mediaType="application/json",
*       @OA\Schema(
*         @OA\Property(
*           property="firstname",
*           type="string",
*         ),
*         @OA\Property(
*           property="lastname",
*           type="string",
*         ),
*         @OA\Property(
*           property="email",
*           type="string",
*         ),
*         @OA\Property(
*           property="password",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/login",
*     summary="Login user to get JWT bearer token",
*     tags={"User"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="email",
*           type="string",
*         ),
*         @OA\Property(
*           property="password",
*           type="string",
*         ),
*       ),
*     ),
*     @OA\MediaType(
*       mediaType="application/json",
*       @OA\Schema(
*         @OA\Property(
*           property="email",
*           type="string",
*         ),
*         @OA\Property(
*           property="password",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/profile",
*     summary="Get data profile login",
*     tags={"User"},
*     security={{ "bearerAuth": {} }},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/refresh-token",
*     summary="Get Token",
*     tags={"User"},
*     security={{ "bearerAuth": {} }},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/logout",
*     summary="User logout",
*     tags={"User"},
*     security={{ "bearerAuth": {} }},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/rabfail",
*     summary="Get data line-chart",
*     tags={"Rabfail"},
*       @OA\Parameter(
*           name="mode",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="start",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="stop",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/triDay",
*     summary="Get data line-chart interval 3 day",
*     tags={"Rabfail"},
*       @OA\Parameter(
*           name="bulan",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/listRtp",
*     summary="Get data list RTP",
*     tags={"Rabfail"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="tanggal",
*           type="string"
*         ),
*       ),
*     ),
*     @OA\MediaType(
*       mediaType="application/json",
*       @OA\Schema(
*         @OA\Property(
*           property="tanggal",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/list50",
*     summary="Get 50 data list monitoring",
*     tags={"Rabfail"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="tanggal",
*           type="string"
*         ),
*       ),
*     ),
*     @OA\MediaType(
*       mediaType="application/json",
*       @OA\Schema(
*         @OA\Property(
*           property="tanggal",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/exportRabfail",
*     summary="Get export rabfail",
*     tags={"Rabfail"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="tanggal",
*           type="string"
*         ),
*         @OA\Property(
*           property="rtp",
*           type="string"
*         ),
*       ),
*     ),
*     @OA\MediaType(
*       mediaType="application/json",
*       @OA\Schema(
*         @OA\Property(
*           property="tanggal",
*           type="string",
*         ),
*         @OA\Property(
*           property="rtp",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/detailRTP",
*     summary="Get detail RTP",
*     tags={"Rabfail"},
*       @OA\Parameter(
*           name="tanggal",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="rtp",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="category",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/scr_mss",
*     summary="Get data SCR and MSS",
*     tags={"Dashboards Performance"},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/pdp_sr",
*     summary="Get data PDR and SR",
*     tags={"Dashboards Performance"},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/scr_ccr_graph",
*     summary="Get Data for Graph SCR and CCR",
*     tags={"Dashboards Performance"},
*       @OA\Parameter(
*           name="mode",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/pdp_sr_graph",
*     summary="Get Data for Graph PDP and SR",
*     tags={"Dashboards Performance"},
*       @OA\Parameter(
*           name="mode",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/ggsn",
*     summary="Get Data All Graph GGSN",
*     tags={"Dashboards Performance"},
*       @OA\Parameter(
*           name="mode",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="ggsn",
*           in="query",
*           required=false,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="apn",
*           in="query",
*           required=false,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="area",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Get(
*     path="/ggsn_fan_temp",
*     summary="Get Data Fan Speed and Temperature",
*     tags={"Dashboards Performance"},
*       @OA\Parameter(
*           name="mode",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="ggsn",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="area",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/ggsn_dropdown",
*     summary="Get list data for dropdown",
*     tags={"Dashboards Performance"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="areain",
*           type="string",
*         ),
*         @OA\Property(
*           property="mode",
*           type="string",
*         ),
*       ),
*     ),
*     @OA\MediaType(
*       mediaType="application/json",
*       @OA\Schema(
*         @OA\Property(
*           property="areain",
*           type="string",
*         ),
*         @OA\Property(
*           property="mode",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/neName_dropdown",
*     summary="Get list data ne_Name",
*     tags={"Dashboards Performance"},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/cek_login",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="username",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Get(
*     path="/menu",
*     summary="Get list menu templates",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="roles",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="link",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Get(
*     path="/tableRegion",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="keyword_page",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Get(
*     path="/columnName",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="table_obc_msisdn",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Get(
*     path="/hitungRetrieve",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id_branch",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="id_users",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="table_obc_msisdn",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="aksi",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="username",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Get(
*     path="/ajaxMsisdn",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="type",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="table_obc_msisdn",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="id_users",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="flag",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="status_claim",
*           in="query",
*           required=false,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="region",
*           in="query",
*           required=false,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="column",
*           in="query",
*           required=false,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="id_branch",
*           in="query",
*           required=false,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="start_date",
*           in="query",
*           required=false,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="end_date",
*           in="query",
*           required=false,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/saveListMsisdn",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="id_users",
*           type="string"
*         ),
*         @OA\Property(
*           property="msisdnkirim",
*           type="string",
*         ),
*         @OA\Property(
*           property="hasil",
*           type="string",
*         ),
*         @OA\Property(
*           property="catatan",
*           type="string",
*         ),
*         @OA\Property(
*           property="flag",
*           type="string"
*         ),
*         @OA\Property(
*           property="table_obc_msisdn",
*           type="string",
*         ),
*         @OA\Property(
*           property="brand",
*           type="string",
*         ),
*         @OA\Property(
*           property="jamklik",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Post(
*     path="/updateListMsisdn",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="id_users",
*           type="string"
*         ),
*         @OA\Property(
*           property="msisdnkirim",
*           type="string",
*         ),
*         @OA\Property(
*           property="hasil",
*           type="string",
*         ),
*         @OA\Property(
*           property="table_obc_msisdn",
*           type="string",
*         ),
*         @OA\Property(
*           property="jamklik",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Post(
*     path="/export_mapping_msisdn",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="ftype",
*           type="string"
*         ),
*         @OA\Property(
*           property="flag",
*           type="string",
*         ),
*         @OA\Property(
*           property="table",
*           type="string",
*         ),
*         @OA\Property(
*           property="id_user",
*           type="string",
*         ),
*         @OA\Property(
*           property="id_branch",
*           type="string",
*         ),
*         @OA\Property(
*           property="part",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/list_cluster",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id_branch",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Get(
*     path="/list_wlupload",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="username",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="flag",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Get(
*     path="/list_wlupload_wb",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="username",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="flag",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Get(
*     path="/list_achive_top10",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="keyword_page",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/insert_upload_wl",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="file_csv",
*           type="file"
*         ),
*         @OA\Property(
*           property="flag",
*           type="string",
*         ),
*         @OA\Property(
*           property="id_region",
*           type="string",
*         ),
*         @OA\Property(
*           property="tap_user",
*           type="string",
*         ),
*         @OA\Property(
*           property="table_name",
*           type="string",
*         ),
*         @OA\Property(
*           property="username",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Post(
*     path="/export_achiev_wl",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="ftype",
*           type="string"
*         ),
*         @OA\Property(
*           property="id_branch",
*           type="string",
*         ),
*         @OA\Property(
*           property="roles",
*           type="string",
*         ),
*         @OA\Property(
*           property="username",
*           type="string",
*         ),
*         @OA\Property(
*           property="mode",
*           type="string",
*         ),
*         @OA\Property(
*           property="flag",
*           type="string",
*         ),
*         @OA\Property(
*           property="start_date",
*           type="string",
*         ),
*         @OA\Property(
*           property="end_date",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Post(
*     path="/tot_info_achiev",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="keyword_page",
*           type="string"
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/users_branch_cluster",
*     summary="",
*     tags={"Chamber"},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Get(
*     path="/list_achive_top10_wabranch",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="keyword_page",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Post(
*     path="/export_achiev_wabranch",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="ftype",
*           type="string"
*         ),
*         @OA\Property(
*           property="mode",
*           type="string",
*         ),
*         @OA\Property(
*           property="flag",
*           type="string",
*         ),
*         @OA\Property(
*           property="start_date",
*           type="string",
*         ),
*         @OA\Property(
*           property="end_date",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/tot_info_achiev_wabranch",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="keyword_page",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/export_achiev",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="roles",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="username",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="mode",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="week",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="year",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="flag",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="start_date",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="end_date",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/obc_per_cluster_achiev",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="keyword_page",
*           type="string"
*         ),
*         @OA\Property(
*           property="regional",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/upload_file_wabranch",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="upload_file",
*           type="file"
*         ),
*         @OA\Property(
*           property="optradio",
*           type="string",
*         ),
*         @OA\Property(
*           property="multi_opt",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/stock_wl_recap",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="ftype",
*           type="string"
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/save_adm_menu",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="namaMenu",
*           type="string"
*         ),
*         @OA\Property(
*           property="iconMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="urlMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="levelMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="parentMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="typeMenu",
*           type="string"
*         ),
*         @OA\Property(
*           property="targetMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="orderMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="colorMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="authorized",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Put(
*     path="/update_adm_menu/{id}",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="namaMenu",
*           type="string"
*         ),
*         @OA\Property(
*           property="iconMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="urlMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="levelMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="parentMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="typeMenu",
*           type="string"
*         ),
*         @OA\Property(
*           property="targetMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="orderMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="colorMenu",
*           type="string",
*         ),
*         @OA\Property(
*           property="status_menu",
*           type="string",
*         ),
*         @OA\Property(
*           property="authorized",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Delete(
*     path="/delete_adm_menu/{id}",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/save_adm_loader",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="namaLoader",
*           type="string"
*         ),
*         @OA\Property(
*           property="sessionLoader",
*           type="string",
*         ),
*         @OA\Property(
*           property="permission",
*           type="string",
*         ),
*         @OA\Property(
*           property="pathLoader",
*           type="string",
*         ),
*         @OA\Property(
*           property="titleLoader",
*           type="string",
*         ),
*         @OA\Property(
*           property="username",
*           type="string"
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Put(
*     path="/update_adm_loader/{id}",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="namaLoader",
*           type="string"
*         ),
*         @OA\Property(
*           property="sessionLoader",
*           type="string",
*         ),
*         @OA\Property(
*           property="permission",
*           type="string",
*         ),
*         @OA\Property(
*           property="pathLoader",
*           type="string",
*         ),
*         @OA\Property(
*           property="titleLoader",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Delete(
*     path="/delete_adm_loader/{id}",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/boopati_loader",
*     summary="",
*     tags={"Chamber"},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Get(
*     path="/chamber_menu",
*     summary="",
*     tags={"Chamber"},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Get(
*     path="/history_login",
*     summary="",
*     tags={"Chamber"},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/save_adm_userTDC",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="usernametdc",
*           type="string"
*         ),
*         @OA\Property(
*           property="tdc_select",
*           type="string",
*         ),
*         @OA\Property(
*           property="rolestdc",
*           type="string",
*         ),
*         @OA\Property(
*           property="username",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Put(
*     path="/update_adm_userTDC/{id}",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="usernametdc",
*           type="string"
*         ),
*         @OA\Property(
*           property="tdc_select",
*           type="string",
*         ),
*         @OA\Property(
*           property="rolestdc",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Delete(
*     path="/delete_adm_userTDC/{id}",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/save_adm_user",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="roles",
*           type="string"
*         ),
*         @OA\Property(
*           property="username",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Put(
*     path="/update_adm_user/{id}",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="username",
*           type="string"
*         ),
*         @OA\Property(
*           property="roles",
*           type="string",
*         ),
*         @OA\Property(
*           property="id_cluster",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Delete(
*     path="/delete_adm_user/{id}",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Put(
*     path="/change_password_user/{id}",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="password",
*           type="string"
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/list_user",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="roles",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/list_user_tdc",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="roles",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="id_cluster",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/adm_get_branch",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id_region",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Get(
*     path="/adm_get_cluster",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id_branch",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/adm_get_tdc_id",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id_tdc",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/adm_get_tdc",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id_cluster",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/save_table_map",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="upload_file",
*           type="file"
*         ),
*         @OA\Property(
*           property="roles",
*           type="string",
*         ),
*         @OA\Property(
*           property="table_name",
*           type="string",
*         ),
*         @OA\Property(
*           property="keyword_page",
*           type="string",
*         ),
*         @OA\Property(
*           property="flag_page",
*           type="string",
*         ),
*         @OA\Property(
*           property="header_page",
*           type="string",
*         ),
*         @OA\Property(
*           property="status_table",
*           type="string",
*         ),
*         @OA\Property(
*           property="username",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Put(
*     path="/update_table_map/{id}",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="upload_file",
*           type="file"
*         ),
*         @OA\Property(
*           property="roles",
*           type="string",
*         ),
*         @OA\Property(
*           property="table_name",
*           type="string",
*         ),
*         @OA\Property(
*           property="keyword_page",
*           type="string",
*         ),
*         @OA\Property(
*           property="flag_page",
*           type="string",
*         ),
*         @OA\Property(
*           property="header_page",
*           type="string",
*         ),
*         @OA\Property(
*           property="status_table",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Delete(
*     path="/delete_table_map/{id}",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/list_table_map",
*     summary="",
*     tags={"Chamber"},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/list_flag",
*     summary="",
*     tags={"Chamber"},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/count_msisdn",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="table_obc_msisdn",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*       @OA\Parameter(
*           name="branch_name",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/hitung_retrieve_wb",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="id_users",
*           type="string"
*         ),
*         @OA\Property(
*           property="table_obc_msisdn",
*           type="string",
*         ),
*         @OA\Property(
*           property="branch_name",
*           type="string",
*         ),
*         @OA\Property(
*           property="username",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/save_claim_wb",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="id_users",
*           type="string"
*         ),
*         @OA\Property(
*           property="msisdnkirim",
*           type="string",
*         ),
*         @OA\Property(
*           property="hasil",
*           type="string",
*         ),
*         @OA\Property(
*           property="catatan",
*           type="string",
*         ),
*         @OA\Property(
*           property="flag",
*           type="string",
*         ),
*         @OA\Property(
*           property="table_obc_msisdn",
*           type="string",
*         ),
*         @OA\Property(
*           property="brand",
*           type="string",
*         ),
*         @OA\Property(
*           property="jamklik",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/update_claim_wb",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="id_users",
*           type="string"
*         ),
*         @OA\Property(
*           property="msisdnkirim",
*           type="string",
*         ),
*         @OA\Property(
*           property="hasil",
*           type="string",
*         ),
*         @OA\Property(
*           property="catatan",
*           type="string",
*         ),
*         @OA\Property(
*           property="jamklik",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/


/**
* @OA\Get(
*     path="/list_cluster_all",
*     summary="",
*     tags={"Chamber"},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/


/**
* @OA\Get(
*     path="/list_region",
*     summary="",
*     tags={"Chamber"},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/insert_upload_wl_wb",
*     summary="",
*     tags={"Chamber"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="file_csv",
*           type="file"
*         ),
*         @OA\Property(
*           property="flag",
*           type="string",
*         ),
*         @OA\Property(
*           property="id_region",
*           type="string",
*         ),
*         @OA\Property(
*           property="tap_user",
*           type="string",
*         ),
*         @OA\Property(
*           property="table_name",
*           type="string",
*         ),
*         @OA\Property(
*           property="username",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/loader_users",
*     summary="",
*     tags={"Chamber"},
*       @OA\Parameter(
*           name="nama_loader",
*           in="query",
*           required=false,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/ansel/cek_login",
*     summary="",
*     tags={"Ansel"},
*       @OA\Parameter(
*           name="username",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Delete(
*     path="/ansel/delete_project/{id}",
*     summary="",
*     tags={"Ansel"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/ansel/list_master",
*     summary="",
*     tags={"Ansel"},
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/ansel/list_configure",
*     summary="",
*     tags={"Ansel"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         required={"nama_roles", "id"},
*         @OA\Property(
*           property="nama_roles",
*           type="string",
*         ),
*         @OA\Property(
*           property="id",
*           type="integer",
*         ),
*       ),
*     ),
*    ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/ansel/list_hadiah",
*     summary="",
*     tags={"Ansel"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         required={"nama_roles", "id"},
*         @OA\Property(
*           property="nama_roles",
*           type="string",
*         ),
*         @OA\Property(
*           property="id",
*           type="integer",
*         ),
*       ),
*     ),
*    ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/ansel/list_peserta",
*     summary="",
*     tags={"Ansel"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         required={"nama_roles", "id"},
*         @OA\Property(
*           property="nama_roles",
*           type="string",
*         ),
*         @OA\Property(
*           property="id",
*           type="integer",
*         ),
*       ),
*     ),
*    ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/ansel/project_exist",
*     summary="",
*     tags={"Ansel"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         required={"project", "type"},
*         @OA\Property(
*           property="nama_roles",
*           type="string",
*         ),
*         @OA\Property(
*           property="project",
*           type="string",
*         ),
*         @OA\Property(
*           property="type",
*           type="string",
*         ),
*         @OA\Property(
*           property="id",
*           type="integer",
*         ),
*       ),
*     ),
*    ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/ansel/list_project_edit/{id}",
*     summary="",
*     tags={"Ansel"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/ansel/save_update_project/{id}",
*     summary="Update Project",
*     tags={"Ansel"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=false,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         required={"aksi","user_created","name_project","description","field_a","field_b","field_c","hadiah"},
*         @OA\Property(
*           property="aksi",
*           type="string"
*         ),
*         @OA\Property(
*           property="user_created",
*           type="string"
*         ),
*         @OA\Property(
*           property="name_project",
*           type="string",
*         ),
*         @OA\Property(
*           property="description",
*           type="string",
*         ),
*         @OA\Property(
*           property="field_a",
*           type="string",
*         ),
*         @OA\Property(
*           property="field_b",
*           type="string",
*         ),
*         @OA\Property(
*           property="field_c",
*           type="string"
*         ),
*         @OA\Property(
*           property="hadiah",
*           type="array",
*               @OA\Items()
*         ),
*         @OA\Property(
*           property="file",
*           type="file",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/
/**
* @OA\Post(
*     path="/ansel/save_update_project",
*     summary="Save Project",
*     tags={"Ansel"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         required={"aksi","user_created","name_project","description","field_a","field_b","field_c","hadiah","file"},
*         @OA\Property(
*           property="aksi",
*           type="string"
*         ),
*         @OA\Property(
*           property="user_created",
*           type="string"
*         ),
*         @OA\Property(
*           property="name_project",
*           type="string",
*         ),
*         @OA\Property(
*           property="description",
*           type="string",
*         ),
*         @OA\Property(
*           property="field_a",
*           type="string",
*         ),
*         @OA\Property(
*           property="field_b",
*           type="string",
*         ),
*         @OA\Property(
*           property="field_c",
*           type="string"
*         ),
*         @OA\Property(
*           property="hadiah",
*           type="array",
*               @OA\Items()
*         ),
*         @OA\Property(
*           property="file",
*           type="file",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/ansel/list_user_dropdown/{id}",
*     summary="",
*     tags={"Ansel"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/


/**
* @OA\Post(
*     path="/ansel/add_user",
*     summary="",
*     tags={"Ansel"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         required={"user_id","username","fullname","password"},
*         @OA\Property(
*           property="user_id",
*           type="string",
*         ),
*         @OA\Property(
*           property="username",
*           type="integer",
*         ),
*         @OA\Property(
*           property="fullname",
*           type="string",
*         ),
*         @OA\Property(
*           property="password",
*           type="string",
*         ),
*       ),
*     ),
*    ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/ansel/list_undian/{id}",
*     summary="",
*     tags={"Ansel"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="string"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/ansel/valid_project",
*     summary="",
*     tags={"Ansel"},
*       @OA\Parameter(
*           name="id_project",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*       @OA\Parameter(
*           name="id_user",
*           in="query",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/ansel/list_hadiah_undi/{id}",
*     summary="",
*     tags={"Ansel"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/ansel/angka_jumlah/{id}",
*     summary="",
*     tags={"Ansel"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/ansel/undi_acak_peserta",
*     summary="",
*     tags={"Ansel"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         required={"list","id_hadiah","periode","id_project","nama_roles"},
*         @OA\Property(
*           property="list",
*           type="string",
*         ),
*         @OA\Property(
*           property="id_hadiah",
*           type="integer",
*         ),
*         @OA\Property(
*           property="periode",
*           type="string",
*         ),
*         @OA\Property(
*           property="id_project",
*           type="string",
*         ),
*         @OA\Property(
*           property="nama_roles",
*           type="string",
*         ),
*       ),
*     ),
*    ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/ansel/undi_get_pemenang",
*     summary="",
*     tags={"Ansel"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         required={"nama_roles","project","kategori"},
*         @OA\Property(
*           property="nama_roles",
*           type="string",
*         ),
*         @OA\Property(
*           property="project",
*           type="integer",
*         ),
*         @OA\Property(
*           property="kategori",
*           type="string",
*         ),
*       ),
*     ),
*    ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Post(
*     path="/ansel/undi_get_peserta",
*     summary="",
*     tags={"Ansel"},
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         required={"project","nama_roles","id_user"},
*         @OA\Property(
*           property="project",
*           type="integer",
*         ),
*         @OA\Property(
*           property="nama_roles",
*           type="string",
*         ),
*         @OA\Property(
*           property="id_user",
*           type="integer",
*         ),
*       ),
*     ),
*    ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Delete(
*     path="/ansel/pemenang_delete_all/{id}",
*     summary="",
*     tags={"Ansel"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Put(
*     path="/pemenang_delete_satu/{id}",
*     summary="",
*     tags={"Ansel"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*   @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/x-www-form-urlencoded",
*       @OA\Schema(
*         @OA\Property(
*           property="id_list_pemenang",
*           type="string"
*         ),
*         @OA\Property(
*           property="id_peserta",
*           type="string",
*         ),
*       ),
*     ),
*   ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/ansel/field_list_pemenang/{id}",
*     summary="",
*     tags={"Ansel"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/

/**
* @OA\Get(
*     path="/ansel/export_pemenang/{id}",
*     summary="",
*     tags={"Ansel"},
*       @OA\Parameter(
*           name="id",
*           in="path",
*           required=true,
*           @OA\Schema(
*                 type="integer"
*           )
*       ),
*    @OA\Response(
*      response=200,
*       description="Success",
*   ),
*   @OA\Response(
*      response=400,
*      description="Bad Request"
*   ),
*   @OA\Response(
*      response=401,
*       description="Unauthorize"
*   ),
*   @OA\Response(
*      response=404,
*      description="Not Found"
*   ),
*   @OA\Response(
*       response=403,
*       description="Forbidden"
*   ),
*   @OA\Response(
*       response=500,
*       description="Server Error"
*   )
* )
*/



class Annotation extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    //  security={{ "bearerAuth": {} }},
}
