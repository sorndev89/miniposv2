<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
// Removed Log facade import, as logging is no longer directly in this basic version
// use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{


    /**
     * ຈັດການການເຂົ້າສູ່ລະບົບ ແລະ ສ້າງ JWT token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // ກວດສອບຂໍ້ມູນທີ່ສົ່ງມາ: email, password, ແລະ remember (ເປັນ boolean)
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        // ດຶງຄ່າ remember ຈາກ request, ຖ້າບໍ່ມີໃຫ້ເປັນ false
        $remember = $request->input('remember', false);

        // ກຳນົດເວລາໝົດອາຍຸຂອງ token (TTL) ຕາມຄ່າ remember
        // ຖ້າ remember ເປັນ true, token ຈະມີອາຍຸ 1 ອາທິດ (60 ນາທີ * 24 ຊົ່ວໂມງ * 7 ມື້)
        // ຖ້າ remember ເປັນ false, ຈະໃຊ້ TTL ເລີ່ມຕົ້ນທີ່ຕັ້ງຄ່າໄວ້ໃນ config/jwt.php (ປົກກະຕິ 60 ນາທີ)
        $ttl = $remember ? (60 * 24 * 7) : 60;

        // ສ້າງ Credentials ໃໝ່ທີ່ມີແຕ່ email ແລະ password ເພື່ອໃຊ້ໃນການຢືນຢັນຕົວຕົນ
        $loginCredentials = [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ];

        // ພະຍາຍາມຢືນຢັນຕົວຕົນຜູ້ໃຊ້ດ້ວຍ Credentials ທີ່ໃຫ້ມາ
        // Auth::guard('api')->setTTL($ttl) ຕັ້ງເວລາໝົດອາຍຸຂອງ token ແບບໄດນາມິກ
        if (! $token = auth()->setTTL($ttl)->attempt($loginCredentials)) {
            // ຖ້າຢືນຢັນຕົວຕົນບໍ່ສຳເລັດ, ໃຫ້ສົ່ງຄືນ Response 401 Unauthorized
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // ຖ້າຢືນຢັນຕົວຕົນສຳເລັດ, ສົ່ງຄືນ token ພ້ອມຂໍ້ມູນຜູ້ໃຊ້
        return $this->respondWithToken($token);
    }

    /**
     * ດຶງຂໍ້ມູນຜູ້ໃຊ້ທີ່ໄດ້ເຂົ້າສູ່ລະບົບແລ້ວ.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function user()
    {
        return response()->json(auth()->user());
    }

    /**
     * ອອກຈາກລະບົບ (Invalidate token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // Invalidates token ປັດຈຸບັນໃນ Blacklist ຂອງ JWT
        auth()->logout();

        return response()->json(['message' => 'ອອກຈາກລະບົບສຳເລັດ!']);
    }

    /**
     * Refresh Token (ສ້າງ token ໃໝ່).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        // ສ້າງ Access Token ໃໝ່ຈາກ Token ທີ່ມີຢູ່ (ຖ້າມັນຍັງຖືກຕ້ອງ)
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * ສ້າງໂຄງສ້າງການຕອບກັບ API ພ້ອມ token ແລະຂໍ້ມູນຜູ້ໃຊ້.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token, // Access Token
            'token_type' => 'bearer',  // ປະເພດ Token
            // ດຶງເວລາໝົດອາຍຸຕົວຈິງຂອງ Token ທີ່ຖືກສ້າງຂຶ້ນ
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60, // ເວລາໝົດອາຍຸໃນວິນາທີ
            'user' => auth()->user(), // ຂໍ້ມູນຜູ້ໃຊ້
        ]);
    }
}
