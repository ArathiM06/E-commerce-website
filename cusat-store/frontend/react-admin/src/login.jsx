import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";

function Login() {
    const navigate = useNavigate();

    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [errorMsg, setErrorMsg] = useState("");

    // Redirect if already logged in
    useEffect(() => {
        const token = localStorage.getItem("token");

        if (token) {
            navigate("/");
        }
    }, [navigate]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrorMsg("");

        const loginData = new URLSearchParams();
        loginData.append("username", email);
        loginData.append("password", password);

        try {
            const response = await fetch(
                "http://localhost:8000/api/auth/login",
                {
                    method: "POST",
                    headers: {
                        "Content-Type":
                            "application/x-www-form-urlencoded",
                    },
                    body: loginData,
                }
            );

            const data = await response.json();

            if (!response.ok) {
                setErrorMsg(
                    data.detail || "Invalid email or password."
                );
                return;
            }

            // Store token and user data
            localStorage.setItem(
                "token",
                data.access_token
            );

            localStorage.setItem(
                "user",
                JSON.stringify(data.user)
            );

            // Store admin info
            if (data.user?.is_admin) {
                localStorage.setItem(
                    "is_admin",
                    "true"
                );

                localStorage.setItem(
                    "admin_token",
                    data.access_token
                );
            }

            // Redirect to homepage
            navigate("/");
        } catch (error) {
            setErrorMsg(
                "Connection error. Is FastAPI running?"
            );
        }
    };

    return (
        <>
            <main className="container page-main">
                <div className="auth-card-container">

                    <h2 className="auth-card-title">
                        Welcome Back
                    </h2>

                    <p className="auth-card-subtitle">
                        Sign in to your account to place orders
                    </p>

                    <hr className="title-underline-hr" />
                    <br />

                    {errorMsg && (
                        <>
                            <div className="auth-error-alert">
                                <b>Login Failed:</b> {errorMsg}
                            </div>
                            <br />
                        </>
                    )}

                    <form onSubmit={handleSubmit}>

                        <div className="form-group">
                            <label className="form-label">
                                Email Address
                            </label>

                            <input
                                type="email"
                                className="form-input"
                                placeholder="Enter your CUSAT email"
                                value={email}
                                onChange={(e) =>
                                    setEmail(e.target.value)
                                }
                                required
                            />
                        </div>

                        <div className="form-group">
                            <label className="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                className="form-input"
                                placeholder="••••••••"
                                value={password}
                                onChange={(e) =>
                                    setPassword(e.target.value)
                                }
                                required
                            />
                        </div>

                        <br />

                        <button
                            type="submit"
                            className="btn btn-teal full-width-input-btn"
                        >
                            Sign In
                        </button>
                    </form>

                    <br />

                    <p className="auth-footer-redirect-text">
                        Don't have an account?{" "}
                        <a
                            href="/register"
                            className="auth-redirect-link"
                        >
                            Register here
                        </a>
                    </p>

                </div>
            </main>

            <br />
            <br />
            <hr />

            <footer className="global-page-footer">
                <center>
                    <p className="footer-brand-text">
                        <b>CUSAT Store Auth Gateway</b>
                    </p>

                    <p className="footer-copyright-text">
                        © 2026 Cochin University of Science and
                        Technology. All rights reserved.
                    </p>
                </center>
            </footer>
        </>
    );
}

export default Login;