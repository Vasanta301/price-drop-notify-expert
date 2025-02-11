import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Dashicon } from '@wordpress/components';
const PriceDropTable = () => {
    const [posts, setPosts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const navigate = useNavigate(); // React Router navigation
    const base_url = `/wp-json/pricedropnotifexpert/v1`
    useEffect(() => {
        const fetchPosts = async () => {
            try {
                const response = await fetch(`${base_url}/notifications`);
                if (!response.ok) {
                    throw new Error("Failed to fetch posts");
                }
                const data = await response.json();
                setPosts(data);
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };

        fetchPosts();
    }, []);

    const handleDelete = async (id) => {
        if (!window.confirm("Are you sure you want to delete this post?")) return;

        try {
            const response = await fetch(`${base_url}/notification/${id}`, {
                method: "DELETE",
                credentials: "same-origin"
            });

            if (!response.ok) {
                console.log(response);
                if (response.status === 401) {
                    throw new Error("Sorry, you are not allowed to delete this post.");
                }
                throw new Error("Failed to delete post");
            }

            setPosts((prevPosts) => prevPosts.filter((post) => post.id !== id));
        } catch (error) {
            alert("Error deleting post: " + error.message);
        }
    };

    if (loading) return <p>Loading...</p>;
    if (error) return <p>Error: {error}</p>;

    return (
        <div className="container">
            <header>
                <div class="header-wrapper">
                    <div class="header-title">
                        <h1>Price Drop Notifications</h1>
                        <p>Welcome to Price Drop Notification landing page.</p>
                    </div>
                </div>
                <div class="header-action">
                    <button className="add-btn btn btn-main" onClick={() => navigate("/add")}>+ Add New</button>
                </div>
            </header>

            <sections id="welcome">
                <div className="content">
                    <h1>Hi there !</h1>
                    <p>You can configure your notify template from here</p>
                </div>
            </sections>
            <sections>
                <h3 class="section-head">Overview</h3>
                <div className="price-drop-list">
                    {posts.map((post) => (
                        <div key={post.id} className="price-drop-item">
                            <div className="left-section">
                                <div className="price-drop-title">{post.title}</div>
                            </div>
                            <div className="action-buttons">
                                <button className="edit-btn" onClick={() => navigate(`/edit/${post.id}`)}><Dashicon icon="edit" /></button>
                                <button className="delete-btn" onClick={() => handleDelete(post.id)}><Dashicon icon="trash" /></button>
                                <button className="preview-btn" onClick={() => window.open(post.link, "_blank")}><Dashicon icon="format-aside" /></button>
                            </div>
                        </div>
                    ))}
                </div>
            </sections>
        </div>
    );
};

export default PriceDropTable;
