    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up(): void
        {
            Schema::create('featured_clients', function (Blueprint $table) {
                $table->id();
                $table->string('name');      // اسم العميل أو الشركة
                $table->string('logo');      // مسار صورة اللوجو
                $table->string('website')->nullable(); // رابط الموقع (اختياري)
                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('featured_clients');
        }
    };
