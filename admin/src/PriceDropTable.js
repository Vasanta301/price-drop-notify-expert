import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Dashicon } from '@wordpress/components';

const PriceDropTable = () => {
    const [posts, setPosts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const navigate = useNavigate(); // React Router navigation
    const base_url = `/wp-json/pricedropnotifexpert/v1`;

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

    const handleEnableDisable = async (id) => {
        try {
            const response = await fetch(`${base_url}/notification/toggle-visibility/${id}`, {
                method: "PUT",
                credentials: "same-origin"
            });

            if (!response.ok) {
                if (response.status === 401) {
                    throw new Error("Sorry, cannot change visibility.");
                }
                throw new Error("Failed to change visibility");
            }

            const updatedPost = await response.json();
            setPosts((prevPosts) =>
                prevPosts.map((post) =>
                    post.id === id ? { ...post, enabled: updatedPost.new_status } : post
                )
            );
        } catch (error) {
            alert("Error changing visibility: " + error.message);
        }
    };

    if (loading) return <p>Loading...</p>;
    if (error) return <p>Error: {error}</p>;

    return (
        <div className="container">
            <header>
                <div className="header-wrapper">
                    <div className="header-title">
                        <h1>Price Drop Notifications</h1>
                        <p>Welcome to Price Drop Notification landing page.</p>
                    </div>
                </div>
                <div className="header-action">
                    <button className="add-btn btn btn-main" onClick={() => navigate("/add")}>+ Add New</button>
                </div>
            </header>

            <section id="welcome">
                <div className="content">
                    <h1>Hi there !</h1>
                    <p>You can configure your notify template from here</p>
                </div>
            </section>
            <section>
                <h3 className="section-head">Overview</h3>
                <div className="price-drop-list">
                    {posts.map((post) => (
                        <div key={post.id} className={`price-drop-item ${post.enabled ? 'visible' : 'hidden'}`}>
                            <div className="left-section">
                                <div className="price-drop-title">{post.title}</div> <span>|</span>
                                <span className="visibility-btn" onClick={() => handleEnableDisable(post.id)}>
                                    <Dashicon icon={post.enabled == 'true' ? "visibility" : "hidden"} />
                                </span>
                            </div>
                            <div className="action-buttons">
                                <button className="edit-btn" onClick={() => navigate(`/edit/${post.id}`)}><Dashicon icon="edit" /></button>
                                <button className="delete-btn" onClick={() => handleDelete(post.id)}><Dashicon icon="trash" /></button>
                            </div>
                        </div>
                    ))}
                </div>
            </section>
        </div>
    );
};

export default PriceDropTable;
