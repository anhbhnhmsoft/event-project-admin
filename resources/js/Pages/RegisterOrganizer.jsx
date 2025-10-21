import { useState } from "react";
import { router } from "@inertiajs/react";
import {
    CheckCircle,
    AlertCircle,
    Loader2,
    Building2,
    Mail,
    Phone,
    Lock,
    Eye,
    EyeOff,
} from "lucide-react";

export default function RegisterOrganizer({ csrf_token }) {
    const [form, setForm] = useState({
        name: "",
        email: "",
        phone: "",
        password: "",
        password_confirmation: "",
    });

    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);
    const [success, setSuccess] = useState(false);
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm({ ...form, [name]: value });

        if (errors[name]) {
            setErrors({ ...errors, [name]: null });
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setLoading(true);
        setErrors({});
        setSuccess(false);

        router.post(
            "/register",
            { ...form, _token: csrf_token },
            {
                onSuccess: () => {
                    setSuccess(true);
                    setForm({
                        name: "",
                        email: "",
                        phone: "",
                        password: "",
                        password_confirmation: "",
                    });

                    setTimeout(() => {
                        window.location.href = "/admin";
                    }, 3000);
                },
                onError: (err) => {
                    setErrors(err);
                    console.error("Registration errors:", err);
                },
                onFinish: () => setLoading(false),
            }
        );
    };

    if (success) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-gray-50">
                <div className="w-full max-w-md p-8 bg-white rounded-xl shadow-sm">
                    <div className="text-center">
                        <div className="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                            <CheckCircle className="w-10 h-10 text-green-600" />
                        </div>
                        <h3 className="text-2xl font-bold text-gray-900 mb-2">
                            Đăng ký thành công!
                        </h3>
                        <p className="text-gray-600 mb-4">
                            Vui lòng kiểm tra email để xác thực tài khoản của
                            bạn.
                        </p>
                        <div className="flex items-center justify-center gap-2 text-sm text-gray-500">
                            <Loader2 className="w-4 h-4 animate-spin" />
                            <span>
                                Đang chuyển hướng đến trang đăng nhập...
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
            <div className="w-full max-w-md">
                <div className="bg-white rounded-xl shadow-sm p-8">
                    {/* Logo Header */}
                    <div className="text-center mb-8">
                        <div className="bg-blue-50 rounded-lg p-6 mb-6">
                            <img
                                src="/images/logo-michec.png"
                                alt="MICHEC"
                                className="mx-auto h-16"
                            />
                        </div>
                        <h2 className="text-2xl font-bold text-gray-900 mb-2">
                            Đăng ký Tổ chức
                        </h2>
                        <p className="text-sm text-gray-600">
                            Tạo tài khoản mới để bắt đầu quản lý sự kiện
                        </p>
                    </div>

                    {/* Form */}
                    <form onSubmit={handleSubmit} className="space-y-4">
                        {/* Organization Name */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1.5">
                                Tên tổ chức{" "}
                                <span className="text-red-500">*</span>
                            </label>
                            <div className="relative">
                                <Building2 className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input
                                    type="text"
                                    name="name"
                                    value={form.name}
                                    onChange={handleChange}
                                    className={`w-full pl-10 pr-3 py-2.5 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition ${
                                        errors.name
                                            ? "border-red-300 bg-red-50"
                                            : "border-gray-300 bg-white"
                                    }`}
                                    placeholder="Nhập tên tổ chức"
                                    required
                                />
                            </div>
                            {errors.name && (
                                <p className="text-xs text-red-600 mt-1.5">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        {/* Email */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1.5">
                                Email <span className="text-red-500">*</span>
                            </label>
                            <div className="relative">
                                <Mail className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input
                                    type="email"
                                    name="email"
                                    value={form.email}
                                    onChange={handleChange}
                                    className={`w-full pl-10 pr-3 py-2.5 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition ${
                                        errors.email
                                            ? "border-red-300 bg-red-50"
                                            : "border-gray-300 bg-white"
                                    }`}
                                    placeholder="example@domain.com"
                                    required
                                />
                            </div>
                            {errors.email && (
                                <p className="text-xs text-red-600 mt-1.5">
                                    {errors.email}
                                </p>
                            )}
                        </div>

                        {/* Phone */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1.5">
                                Số điện thoại{" "}
                                <span className="text-red-500">*</span>
                            </label>
                            <div className="relative">
                                <Phone className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input
                                    type="tel"
                                    name="phone"
                                    value={form.phone}
                                    onChange={handleChange}
                                    className={`w-full pl-10 pr-3 py-2.5 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition ${
                                        errors.phone
                                            ? "border-red-300 bg-red-50"
                                            : "border-gray-300 bg-white"
                                    }`}
                                    placeholder="0123456789"
                                    required
                                />
                            </div>
                            {errors.phone && (
                                <p className="text-xs text-red-600 mt-1.5">
                                    {errors.phone}
                                </p>
                            )}
                        </div>

                        {/* Password */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1.5">
                                Mật khẩu <span className="text-red-500">*</span>
                            </label>
                            <div className="relative">
                                <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input
                                    type={showPassword ? "text" : "password"}
                                    name="password"
                                    value={form.password}
                                    onChange={handleChange}
                                    className={`w-full pl-10 pr-10 py-2.5 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition ${
                                        errors.password
                                            ? "border-red-300 bg-red-50"
                                            : "border-gray-300 bg-white"
                                    }`}
                                    placeholder="Tối thiểu 6 ký tự"
                                    required
                                />
                                <button
                                    type="button"
                                    onClick={() =>
                                        setShowPassword(!showPassword)
                                    }
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                >
                                    {showPassword ? (
                                        <EyeOff className="w-4 h-4" />
                                    ) : (
                                        <Eye className="w-4 h-4" />
                                    )}
                                </button>
                            </div>
                            {errors.password && (
                                <p className="text-xs text-red-600 mt-1.5">
                                    {errors.password}
                                </p>
                            )}
                        </div>

                        {/* Confirm Password */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1.5">
                                Xác nhận mật khẩu{" "}
                                <span className="text-red-500">*</span>
                            </label>
                            <div className="relative">
                                <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input
                                    type={
                                        showConfirmPassword
                                            ? "text"
                                            : "password"
                                    }
                                    name="password_confirmation"
                                    value={form.password_confirmation}
                                    onChange={handleChange}
                                    className={`w-full pl-10 pr-10 py-2.5 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition ${
                                        errors.password_confirmation
                                            ? "border-red-300 bg-red-50"
                                            : "border-gray-300 bg-white"
                                    }`}
                                    placeholder="Nhập lại mật khẩu"
                                    required
                                />
                                <button
                                    type="button"
                                    onClick={() =>
                                        setShowConfirmPassword(
                                            !showConfirmPassword
                                        )
                                    }
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                >
                                    {showConfirmPassword ? (
                                        <EyeOff className="w-4 h-4" />
                                    ) : (
                                        <Eye className="w-4 h-4" />
                                    )}
                                </button>
                            </div>
                            {errors.password_confirmation && (
                                <p className="text-xs text-red-600 mt-1.5">
                                    {errors.password_confirmation}
                                </p>
                            )}
                        </div>

                        {/* Submit Button */}
                        <button
                            type="submit"
                            disabled={loading}
                            className="w-full bg-blue-600 text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors duration-200 flex items-center justify-center gap-2 mt-6"
                        >
                            {loading ? (
                                <>
                                    <Loader2 className="w-4 h-4 animate-spin" />
                                    <span>Đang xử lý...</span>
                                </>
                            ) : (
                                <span>Đăng ký ngay</span>
                            )}
                        </button>
                    </form>

                    {/* Footer */}
                    <div className="mt-6 text-center">
                        <p className="text-sm text-gray-600">
                            Đã có tài khoản?{" "}
                            <a
                                href="/admin"
                                className="text-blue-600 hover:text-blue-700 font-medium hover:underline"
                            >
                                Đăng nhập ngay
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
