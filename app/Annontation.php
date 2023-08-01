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
 *     schemes={"https://10.59.105.208/api-rabfail/api/v1/"},
 *     @OA\Info(
 *         version="1.0.0",
 *         title="API documentation"
 *     )
 * )
 */

 /**
*  @OA\Server(
*      url="https://10.59.105.208/api-rabfail/api/v1/",
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

class Annotation extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    //  security={{ "bearerAuth": {} }},
}
