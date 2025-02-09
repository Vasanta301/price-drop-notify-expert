import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

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
            <div className="header-section">
                <h2>Price Drop Notifications</h2>
                <button className="add-btn" onClick={() => navigate("/add")}>+ Add New</button>
            </div>
            <div className="price-drop-list">
                {posts.map((post) => (

                    <div key={post.id} className="price-drop-item">
                        <div className="left-section">
                            <div className="price-drop-title">{post.title}</div>
                        </div>
                        <div className="action-buttons">
                            <button className="edit-btn" onClick={() => navigate(`/edit/${post.id}`)}>Edit</button>
                            <button className="delete-btn" onClick={() => handleDelete(post.id)}>Delete</button>
                            <button className="preview-btn" onClick={() => window.open(post.link, "_blank")}>Preview</button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default PriceDropTable;
