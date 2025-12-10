<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\OrderItem;

/**
 * @class Order
 * @package App\Models
 *
 * @property int $id
 * @property string $order_code ລະຫັດຄໍາສັ່ງຊື້ທີ່ເປັນເອກະລັກ
 * @property \Illuminate\Support\Carbon $order_date ວັນທີຂອງຄໍາສັ່ງຊື້
 * @property int $seller_user_id ໄອດີຂອງຜູ້ໃຊ້ທີ່ຂາຍສິນຄ້າ
 * @property float $total_amount ມູນຄ່າທັງຫມົດຂອງຄໍາສັ່ງຊື້
 * @property float $amount_received ຈໍານວນເງິນທີ່ໄດ້ຮັບຈາກລູກຄ້າ
 * @property float $change_amount ຈໍານວນເງິນທອນທີ່ຕ້ອງຄືນໃຫ້ລູກຄ້າ
 * @property float|null $total_profit ກໍາໄລທັງຫມົດຈາກຄໍາສັ່ງຊື້ (ອາດຈະເປັນ null)
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property \App\Models\User $seller
 * @property \Illuminate\Database\Eloquent\Collection<OrderItem> $items
 */
class Order extends Model
{
    use HasFactory;

    /**
     * ຄຸນລັກສະນະທີ່ສາມາດມອບຫມາຍໄດ້ຫຼາຍ.
     * (The attributes that are mass assignable.)
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_code', // ລະຫັດຄໍາສັ່ງຊື້
        'order_date', // ວັນທີຄໍາສັ່ງຊື້
        'total_amount', // ມູນຄ່າທັງໝົດ
        'seller_user_id', // ໄອດີຜູ້ຂາຍ
        'amount_received', // ເງິນທີ່ໄດ້ຮັບ
        'change_amount', // ເງິນທອນ
        'total_profit', // ກໍາໄລທັງໝົດ
    ];

    /**
     * ຄຸນລັກສະນະທີ່ຄວນຖືກປ່ຽນປະເພດ.
     * (The attributes that should be cast.)
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order_date' => 'datetime', // ປ່ຽນ order_date ເປັນວັນທີເວລາ
        'total_amount' => 'decimal:2', // ປ່ຽນ total_amount ເປັນຈໍານວນທົດສະນິຍົມ 2 ຕໍາແໜ່ງ
        'amount_received' => 'decimal:2', // ປ່ຽນ amount_received ເປັນຈໍານວນທົດສະນິຍົມ 2 ຕໍາແໜ່ງ
        'change_amount' => 'decimal:2', // ປ່ຽນ change_amount ເປັນຈໍານວນທົດສະນິຍົມ 2 ຕໍາແໜ່ງ
        'total_profit' => 'decimal:2', // ປ່ຽນ total_profit ເປັນຈໍານວນທົດສະນິຍົມ 2 ຕໍາແໜ່ງ
    ];

    /**
     * ດຶງຂໍ້ມູນລາຍການສິນຄ້າຂອງຄໍາສັ່ງຊື້.
     * (Get the order items for the order.)
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);  // 
    }

    // Function: items() — ຟັງຊັນນີ້ຢູ່ໃນ Order ແລະເຮັດໜ້າທີ່ດຶງຂໍ້ມູນ OrderItem ທີ່ເກັບຢູ່ໃນຄໍາສັ່ງນັ້ນ.
// Return: ຄ່າທີ່ສົມບັດກັບ HasMany — ບອກວ່າ Order ໜຶ່ງລາຍການອາດຈະມີ OrderItem ຫຼາຍລາຍ.
// Default foreign key: Laravel ຈະຄາດວ່າຟິວ OrderItem ຈະມີຄອນ order_id (ຊື່ຕາມ snake_case + _id) ເພື່ອເຊື່ອມກັບ orders.id ຖ້າບໍ່ລະບຸຄອນ/ພີວ.
// ວິທີໃຊ້:
// ດຶງລາຍການ:  $order->items (ຄືນ Collection)
// ສ້າງລາຍການໃໝ່: $order->items()->create([...])
// ນັບສະຫຼຸບ: $order->items()->sum('subtotal')
// ຄຳແນະນຳ: ຫາກຕ້ອງການຊື່ຄອນຫຼືຄີສ໌ທີ່ແຕກຕ່າງ, ສາມາດປ່ຽນໄດ້ໂດຍ return $this->hasMany(OrderItem::class, 'my_order_id', 'id');
// ແລະຄວນມີຄວາມສັນພັນກັບດ້ານກັບ OrderItem (ຕົວຢ່າງ): public function order(): BelongsTo { return $this->belongsTo(Order::class); }
// ຫມາຍເຫດ: ຟັງຊັນທ້າຍບໍ່ຈໍາເປັນຕ້ອງມີຫຍໍ້ຄໍາອະທິບາຍຕື່ນຕົ້ນອື່ນ — ມັນຊັດເຈນພຍດເປັນ Laravel Eloquent Relationship ທົ່ວໄປ.


    /**
     * ດຶງຂໍ້ມູນຜູ້ໃຊ້ທີ່ຂາຍສິນຄ້າ.
     * (Get the user who sold the order.)
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }


}
