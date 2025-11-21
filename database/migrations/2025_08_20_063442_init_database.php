<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
        // Tạo bảng organizers để lưu trữ thông tin về các nhà tổ chức sự kiện
        Schema::create('organizers', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng organizers lưu trữ các nhà tổ chức sự kiện');
            $table->string('name')->comment('Tên nhà tổ chức');
            $table->string('image')->nullable()->comment('URL hình ảnh đại diện');
            $table->text('description')->nullable()->comment('Mô tả về nhà tổ chức');
            $table->tinyInteger('status')->default(1)->comment('Trạng thái của nhà tổ chức, 1: hoạt động, 0: không hoạt động');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->text('introduce')->nullable();
            $table->tinyInteger('gender')->nullable();
            $table->tinyInteger('role');
            $table->string('avatar_path')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->foreignId('organizer_id')->references('id')->on('organizers')->cascadeOnDelete();
            $table->string('password');
            $table->string('lang', 10);
            $table->unique(['email', 'organizer_id']);
            $table->unique(['phone', 'organizer_id']);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });


        // Tạo bảng membership để lưu trữ các gói membership
        Schema::create('membership', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng membership lưu trữ các gói membership dành cho người dùng');
            $table->string('name')->comment('Tên gói membership');
            $table->string('description')->comment('Mô tả gói membership');
            $table->string('price')->comment('Giá của gói membership');
            $table->integer('duration')->nullable()->comment('Thời gian sử dụng gói membership, tính bằng tháng');
            $table->string('badge')->nullable()->comment("Huy hiệu hiển thị");
            $table->integer('sort')->nullable()->comment("Sắp xếp hiển thị");
            $table->string('badge_color_background')->nullable()->comment("Màu huy hiệu hiển thị trên trang chủ");
            $table->string('badge_color_text')->nullable()->comment("Màu chữ huy hiệu hiển thị trên trang chủ");
            $table->json('config')->comment('Cấu hình của gói membership, lưu trữ các tùy chọn như quyền truy cập, tính năng, v.v.');
            $table->boolean('status')->default(true)->comment('Trạng thái của gói membership, true nếu hoạt động, false nếu không hoạt động');
            $table->tinyInteger('type')
                ->default(1)
                ->comment('Loại gói membership, Lưu trong enum MembershipType');
            $table->foreignId('organizer_id')
                ->constrained('organizers')
                ->onDelete('cascade')
                ->comment('Khóa ngoại tới bảng organizer');
            $table->softDeletes();
            $table->timestamps();
        });



        Schema::create('membership_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('membership_id')
                ->constrained('membership')
                ->cascadeOnDelete();
            $table->date('start_date')->nullable()
                ->comment('Ngày bắt đầu gói membership');
            $table->date('end_date')->nullable()
                ->comment('Ngày kết thúc gói membership');
            $table->tinyInteger('status')->comment('Trạng thái gói: enum định nghĩa MembershipUserStatus');
            $table->timestamps();
        });

        Schema::create('membership_organizer', function (Blueprint $table) {

            $table->id();
            $table->foreignId('organizer_id')
                ->constrained('organizers')
                ->cascadeOnDelete();
            $table->foreignId('membership_id')
                ->constrained('membership')
                ->cascadeOnDelete();
            $table->date('start_date')->nullable()
                ->comment('Ngày bắt đầu gói membership');
            $table->date('end_date')->nullable()
                ->comment('Ngày kết thúc gói membership');
            $table->tinyInteger('status')->comment('Trạng thái gói: enum định nghĩa MembershipUserStatus');
            $table->timestamps();
        });

        // Tạo bảng transactions để lưu trữ các giao dịch của người dùng
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng transactions lưu trữ các giao dịch của người dùng');
            // Khóa ngoại liên kết với các bảng, dựa theo type sẽ xác định bảng nào được liên kết
            $table->bigInteger('foreign_id')->comment('ID của đối tượng liên kết, có thể là ID của gói membership hoặc ID của sự kiện');
            $table->tinyInteger('type')->comment('Loại giao dịch, trong enum TransactionType');


            $table->string('money')->comment('Số tiền giao dịch');

            // Mã giao dịch từ hệ thống (nội bộ)
            $table->string('transaction_code');
            $table->tinyInteger('type_trans')->comment('Loại giao dịch (Casso, Momo,...)');
            $table->string('transaction_id')->nullable()->comment('ID giao dịch từ hệ thống thanh toán bên ngoài');
            $table->string('description')->nullable()->comment('Mô tả giao dịch');
            $table->tinyInteger('status')->comment('Trạng thái giao dịch trong enum TransactionStatus');
            $table->text('metadata')->nullable()->comment('Dữ liệu bổ sung liên quan đến giao dịch, có thể là thông tin bổ sung từ hệ thống thanh toán');
            $table->unique(['type_trans', 'transaction_id']);
            $table->foreignId('user_id')->constrained();
            $table->timestamp('expired_at')->nullable();
            $table->json('config_pay')->nullable();
            $table->foreignId('organizer_id')
                ->constrained('organizers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng provinces để lưu trữ thông tin về các tỉnh thành
        Schema::create('provinces', function (Blueprint $table) {;
            $table->id();
            $table->comment('Bảng provinces lưu trữ các tỉnh thành');
            $table->string('name')->comment('Tên');
            $table->string('code')->unique()->comment('Mã');
            $table->string('division_type')->nullable()->comment('Cấp hành chính');
            $table->timestamps();
        });

        // Tạo bảng districts để lưu trữ thông tin về các quận huyện
        Schema::create('districts', function (Blueprint $table) {;
            $table->id();
            $table->comment('Bảng districts lưu trữ các quận huyện');
            $table->string('name')->comment('Tên');
            $table->string('code')->unique()->comment('Mã');
            $table->string('division_type')->nullable()->comment('Cấp hành chính');
            $table->string('province_code');
            $table->foreign('province_code')->references('code')->on('provinces')->cascadeOnDelete();
            $table->timestamps();
        });

        // Tạo bảng districts để lưu trữ thông tin về các phường xã
        Schema::create('wards', function (Blueprint $table) {;
            $table->id();
            $table->comment('Bảng ward lưu trữ các phường xã');
            $table->string('name')->comment('Tên');
            $table->string('code')->unique()->comment('Mã');
            $table->string('division_type')->nullable()->comment('Cấp hành chính');

            // Khóa ngoại nối bằng code
            $table->string('district_code');
            $table->foreign('district_code')->references('code')->on('districts')->cascadeOnDelete();
            $table->timestamps();
        });


        // Tạo bảng events để lưu trữ các sự kiện
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng events lưu trữ các sự kiện');

            // Khóa ngoại liên kết với nhà tổ chức
            $table->foreignId('organizer_id')
                ->constrained('organizers')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Thông tin sự kiện
            $table->string('name')->comment('Tên sự kiện');
            $table->text('short_description')->nullable()->comment('Mô tả ngắn gọn của sự kiện');
            $table->text('description')->comment('Mô tả sự kiện');
            $table->dateTime('day_represent')->comment('Ngày tổ chức sự kiện');
            $table->dateTime('start_time')->comment('Thời gian bắt đầu sự kiện');
            $table->dateTime('end_time')->comment('Thời gian kết thúc sự kiện');
            $table->string('image_represent_path')->nullable()->comment('URL hình ảnh đại diện cho sự kiện');
            $table->tinyInteger('status')
                ->comment('Trạng thái của sự kiện, Lưu trong enum EventStatus');

            // Địa điểm sự kiện
            $table->string('address')->comment('Địa chỉ sự kiện');
            $table->string('province_code');
            $table->string('district_code');
            $table->string('ward_code');
            $table->foreign('province_code')->references('code')->on('provinces')->cascadeOnDelete();
            $table->foreign('district_code')->references('code')->on('districts')->cascadeOnDelete();
            $table->foreign('ward_code')->references('code')->on('wards')->cascadeOnDelete();
            $table->decimal('latitude', 10, 6)->comment('Vĩ độ');
            $table->decimal('longitude', 10, 6)->comment('Kinh độ');
            $table->boolean('free_to_join')
                ->default(true)
                ->comment('Sự kiện miễn phí tham gia hay không');
            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng event_user để lưu trữ mối quan hệ giữa người dùng và sự kiện
        Schema::create('event_user', function (Blueprint $table) {
            $table->id();
            // Liên kết user - event
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('role')
                ->comment('Vai trò của người dùng trong sự kiện, Lưu trong enum EventUserRole');
            $table->timestamps();
            $table->unique(['event_id', 'user_id', 'role']); // tránh trùng lặp
        });

        // Tạo bảng event_schedules để lưu trữ lịch trình của các sự kiện
        Schema::create('event_schedules', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng event_schedules lưu trữ lịch trình của các sự kiện');
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('title')->comment('Tiêu đề của lịch trình');
            $table->text('description')->nullable()->comment('Mô tả chi tiết của lịch trình');
            $table->dateTime('start_time')->comment('Thời gian bắt đầu lịch trình');
            $table->dateTime('end_time')->comment('Thời gian kết thúc lịch trình');
            $table->integer('sort')->nullable()->comment('Sắp xếp lịch trình');
            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng event_schedule_documents để lưu trữ các tài liệu liên quan đến lịch trình sự kiện
        Schema::create('event_schedule_documents', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng event_schedule_documents lưu trữ các tài liệu liên quan đến lịch trình sự kiện');
            $table->foreignId('event_schedule_id')->constrained('event_schedules')->cascadeOnDelete();
            $table->string('title')->comment('Tiêu đề của tài liệu');
            $table->text('description')->comment('Mô tả chi tiết về tài liệu');
            $table->unsignedInteger('price')->default(0)->comment('Giá tài liệu nếu giá trị = 0, tài liệu miễn phí');

            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng event_schedule_document_files để lưu trữ các tệp đính kèm của tài liệu lịch trình sự kiện
        Schema::create('event_schedule_document_files', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng event_schedule_document_files lưu trữ các tệp đính kèm của tài liệu lịch trình sự kiện');
            $table->foreignId('event_schedule_document_id')->constrained('event_schedule_documents')->cascadeOnDelete();
            $table->string('file_path')->comment('Đường dẫn đến tệp đính kèm');
            $table->string('file_name')->comment('Tên tệp đính kèm');
            $table->string('file_extension')->comment('Phần mở rộng của tệp đính kèm, ví dụ: pdf, docx, jpg, v.v.');
            $table->string('file_size')->comment('Kích thước tệp đính kèm, lưu trữ dưới dạng chuỗi (ví dụ: "2MB", "500KB")');
            $table->string('file_type')->comment('Loại tệp đính kèm, ví dụ: pdf, docx, jpg, v.v.');
            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng event_schedule_document_user để lưu trữ các file trong lịch trình sự kiện người dùng từng tham gia

        Schema::create('event_schedule_document_user', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng event_schedule_document_user để lưu trữ các file trong lịch trình sự kiện người dùng từng tham gia');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_schedule_document_id')->constrained('event_schedule_documents')->cascadeOnDelete();
            $table->tinyInteger('status')->default(1)->comment('Lưu trạng thái của khách mời đối với tài liệu sự kiện');
            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng event_games để lưu trữ các trò chơi trong lịch trình sự kiện
        Schema::create('event_games', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng event_games lưu trữ các trò chơi trong lịch trình sự kiện');
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name')->comment('Tên trò chơi');
            $table->text('description')->nullable()->comment('Mô tả trò chơi');
            $table->tinyInteger('game_type')->comment('Loại trò chơi, Lưu trong enum EventGameType');
            $table->json('config_game')->comment('Cấu hình trò chơi, lưu trữ các tùy chọn như luật chơi, điểm số, v.v.');
            $table->softDeletes();
            $table->timestamps();
        });


        // Tạo bảng event_game_gifts để lưu trữ các phần quà trong trò chơi của sự kiện
        Schema::create('event_game_gifts', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng event_game_gifts để lưu trữ các phần quà trong trò chơi của sự kiện');
            $table->foreignId('event_game_id')->constrained('event_games')->cascadeOnDelete();
            $table->string('name')->comment('Tên món quà');
            $table->text('description')->nullable()->comment('Mô tả món quà');
            $table->text('image')->nullable()->comment('Hình ảnh món quà');
            $table->integer('quantity')->comment('Số lượng món quà');
            $table->softDeletes();
            $table->timestamps();
        });


        Schema::create('event_user_gift', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng event_user_gift lưu trữ kết quả nhận quà của sự kiện');
            $table->foreignId('event_game_gift_id')->constrained('event_game_gifts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng event_areas để lưu trữ các khu vực trong sự kiện
        Schema::create('event_areas', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng event_areas lưu trữ các khu vực trong sự kiện');
            $table->string('name')->comment('Tên khu vực');
            $table->bigInteger('capacity')->comment('Số lượng ghế trong khu vực');
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->boolean('vip')->default(false);
            $table->string('price')
                ->nullable()
                ->comment('Giá vé khu vực');
            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng event_seats để lưu trữ các ghế trong khu vực sự kiện
        Schema::create('event_seats', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng event_seats lưu trữ các ghế trong khu vực sự kiện');
            $table->foreignId('event_area_id')->constrained('event_areas')->cascadeOnDelete();
            $table->string('seat_code')->comment('Mã ghế, định dạng như A1, B2, C3, ...');
            $table->tinyInteger('status')->comment('Trạng thái ghế, Lưu trong enum EventSeatStatus');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->unique(['event_area_id', 'seat_code']);
            $table->timestamps();
        });

        // Tạo bảng event_comments để lưu trữ các bình luận về sự kiện
        Schema::create('event_comments', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng event_comments lưu trữ các bình luận về sự kiện');
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('type')->comment('Phân loại nội dung bình luận');
            $table->text('content')->comment('Nội dung bình luận');
            $table->timestamps();
        });

        // Tạo bảng event_user_histories để lưu trữ vé sự kiện
        Schema::create('event_user_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_seat_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ticket_code')->nullable()->unique()->comment('Mã vé, định dạng như TICKET-123456');
            $table->tinyInteger('status')->comment('Trạng thái vé trong enum EventUserHistoryStatus');
            $table->json('features')->nullable()->comment('Cấu hình quyền của người dùng trong sự kiện: Bình luận mất phí');
            $table->timestamps();
        });

        // Tạo bảng configs để lưu trữ các cấu hình hệ thống
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('config_key');
            $table->smallInteger('config_type')->nullable()->comment('Loại cấu hình, Lưu trong enum ConfigType');
            $table->text('config_value');
            $table->text('description')->nullable();
            $table->foreignId('organizer_id')
                ->constrained('organizers')
                ->onDelete('cascade')
                ->comment('Khóa ngoại tới bảng organizer')
                ->after('config_type');
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('user_reset_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('code', 6);
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng user_notifications để lưu trữ các thông báo cho người dùng
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->json('data')->nullable()->comment('Dữ liệu thông báo, lưu trữ các dữ liệu liên quan đến thông báo');
            $table->tinyInteger('notification_type')->comment('Loại thông báo, Lưu trong enum UserNotificationType');
            $table->tinyInteger('status')->comment('Trạng thái thông báo, Lưu trong enum UserNotificationStatus');
            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng user_devices để lưu trữ các thiết bị của người dùng
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('expo_push_token')->unique();
            $table->string('device_id')->nullable();
            $table->string('device_type', 20)->nullable();
            $table->dateTime('last_seen_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng event_polls để lưu trữ các cuộc khảo sát/bình chọn
        Schema::create('event_polls', function (Blueprint $table) {
            $table->id();
            $table->comment('Lưu trữ thông tin về một đợt Khảo sát/Bình chọn cụ thể trong sự kiện.');
            $table->foreignId('event_id')
                ->comment('Khóa ngoại liên kết với bảng "events".')
                ->constrained('events')
                ->cascadeOnDelete();
            $table->text('title')->comment('Tiêu đề của cuộc khảo sát.');
            $table->timestamp('start_time')->comment('Thời điểm bắt đầu mở khảo sát.');
            $table->timestamp('end_time')->comment('Thời điểm kết thúc khảo sát.');
            $table->tinyInteger('duration_unit')->comment('Đơn vị duration lưu trữ ở constant type unit duration');
            $table->integer('duration')->comment('Thời lượng (giờ/phút/ngày) kéo dài khảo sát.');
            $table->tinyInteger('is_active')->comment('Trạng thái kích hoạt (1: Active, 0: Inactive).');
            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng event_poll_questions để lưu trữ các câu hỏi trong cuộc khảo sát/bình chọn
        Schema::create('event_poll_questions', function (Blueprint $table) {
            $table->id();
            $table->comment('Lưu trữ các câu hỏi thuộc về một khảo sát/bình chọn.');
            $table->foreignId('event_poll_id')
                ->constrained('event_polls')
                ->cascadeOnDelete();
            $table->tinyInteger('type')->comment('Loại câu hỏi lưu ở constant QuestionType');
            $table->text('question')->comment('Nội dung chi tiết của câu hỏi.');
            $table->tinyInteger('order')->comment('Thứ tự hiển thị của câu hỏi trong khảo sát.');
            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng event_poll_question_options để lưu trữ các câu trả lời cho câu hỏi trong cuộc khảo sát/bình chọn
        Schema::create('event_poll_question_options', function (Blueprint $table) {
            $table->id();
            $table->comment('Lưu trữ các tùy chọn/đáp án cho các câu hỏi dạng trắc nghiệm (Single/Multiple Choice).');
            $table->foreignId('event_poll_question_id')
                ->constrained('event_poll_questions')
                ->cascadeOnDelete();
            $table->char('label', length: 255)->comment('Nội dung của tùy chọn/đáp án.');
            $table->tinyInteger('order')->comment('Thứ tự hiển thị của tùy chọn.');
            $table->softDeletes();
            $table->timestamps();
        });

        // Tạo bảng event_poll_votes để lưu trữ phản hồi/bình chọn từ các câu trả lời cho câu hỏi trong cuộc khảo sát/bình chọn
        Schema::create('event_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->comment('Lưu trữ phản hồi/bình chọn của người dùng đối với các câu hỏi.');
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('event_poll_question_id')
                ->constrained('event_poll_questions')
                ->cascadeOnDelete();
            $table->foreignId('event_poll_question_option_id')
                ->nullable()
                ->comment('Khóa ngoại liên kết với tùy chọn/đáp án đã chọn.')
                ->nullable()
                ->constrained('event_poll_question_options')
                ->cascadeOnDelete();
            $table->string('answer_content')->nullable()->comment('Nội dung câu trả lời của người dùng cho câu hỏi dạng trả lời tự do');
            $table->index(['user_id', 'event_poll_question_id']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configs');
        Schema::dropIfExists('event_user_histories');
        Schema::dropIfExists('event_comments');
        Schema::dropIfExists('event_seats');
        Schema::dropIfExists('event_areas');
        Schema::dropIfExists('event_games');
        Schema::dropIfExists('event_schedule_document_files');
        Schema::dropIfExists('event_schedule_documents');
        Schema::dropIfExists('event_schedule_document_user');
        Schema::dropIfExists('event_schedules');
        Schema::dropIfExists('event_user');
        Schema::dropIfExists('events');
        Schema::dropIfExists('organizers');
        Schema::dropIfExists('ward');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('membership');
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('membership_user');
        Schema::dropIfExists('membership_organizer');
        Schema::dropIfExists('event_game_gifts');
        Schema::dropIfExists('event_user_gift');
        Schema::dropIfExists('user_reset_codes');
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('event_polls');
        Schema::dropIfExists('event_poll_questions');
        Schema::dropIfExists('event_poll_question_options');
        Schema::dropIfExists('event_poll_votes');
    }
};
