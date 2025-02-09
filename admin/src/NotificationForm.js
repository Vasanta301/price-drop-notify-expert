import React, { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import AsyncSelect from 'react-select/async';
const NotificationForm = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [notifMessage, setNotifMessage] = useState(""); // State to store the message
    const [notifType, setNotifType] = useState(""); // "error" or "success"
    const [title, setTitle] = useState("");
    const [content, setContent] = useState("");
    const [trackingType, setTrackingType] = useState("");
    const [startDate, setStartDate] = useState("");
    const [endDate, setEndDate] = useState("");
    const [notificationType, setNotificationType] = useState("info"); // Default type
    const [isEnabled, setIsEnabled] = useState(true); // Default enabled
    const [selectedProducts, setSelectedProducts] = useState([]);
    const [selectedCategories, setSelectedCategories] = useState([]);

    //Set Base URL
    const base_url = `/wp-json/pricedropnotifexpert/v1`;
    useEffect(() => {
        if (id) {
            // Fetch notification data when editing
            fetch(`${base_url}/notification/${id}`)
                .then((res) => res.json())
                .then((data) => {
                    if (Array.isArray(data) && data.length > 0) {
                        setTitle(data[0].title || "");
                        setContent(data[0].content || "");
                        setStartDate(data[0].start_date ? new Date(data[0].start_date).toISOString().split('T')[0] : "");
                        setEndDate(data[0].end_date ? new Date(data[0].end_date).toISOString().split('T')[0] : "");
                        setNotificationType(data[0].type || "info");
                        setIsEnabled(data[0].enabled !== undefined ? data[0].enabled : true);
                        setSelectedProducts(data[0].selected_products || []);
                        setSelectedCategories(data[0].selected_categories || []);
                        setTrackingType(data[0].tracking_type || "all");
                    }
                })
                .catch((err) => console.error("Error fetching notification:", err));
            const loadCategories = async () => {
                const categories = await fetchProductCategories();
                setSelectedCategories(categories);
            };
        }
    }, [id]);


    const fetchProductCategories = async () => {
        try {
            const response = await fetch(
                `/wp-json/wc/v3/products/categories`, // WooCommerce categories endpoint
                {
                    headers: {
                        'X-WP-Nonce': wpApiSettings.nonce, // Use the nonce for authentication
                    },
                }
            );

            if (!response.ok) {
                throw new Error('Failed to fetch product categories');
            }

            const categories = await response.json();
            return categories.map((category) => ({
                value: category.id, // Use category ID as the value
                label: category.name, // Use category name as the label
            }));
        } catch (error) {
            console.error('Error fetching product categories:', error);
            return [];
        }
    };
    const handleSubmit = async (e) => {
        e.preventDefault();

        const method = id ? "PUT" : "POST"; // WordPress uses POST for updates via REST API
        const endpoint = id
            ? `${base_url}/notification/${id}`
            : `${base_url}/notification/`;

        try {
            const response = await fetch(endpoint, {
                method,
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    title,
                    content,
                    tracking_type: trackingType,
                    start_date: startDate,
                    end_date: endDate,
                    type: notificationType,
                    enabled: isEnabled,
                    selected_categories: selectedCategories,
                    selected_products: selectedProducts,
                    status: "publish",
                }),
            });

            if (!response.ok) {
                throw new Error("Failed to save");
            } else {
                console.log(response);
                setNotifMessage("Notification saved successfully!");
                setNotifType("success");

                setTimeout(() => {
                    setNotifMessage(""); // Clear after 3 seconds
                }, 3000);

                // Redirect to the correct route
                if (id) {
                    navigate(`/edit/${id}`);
                } else {
                    // After creation, redirect to the edit page of the new notification
                    const createdData = await response.json();
                    navigate(`/edit/${createdData.id}`);
                }
            }
        } catch (err) {
            console.error(err);
            alert("Error saving notification");
        }
    };

    const loadOptions = (inputValue, callback) => {
        fetchProducts(inputValue).then((options) => callback(options));
    };

    const fetchProducts = async (inputValue) => {
        if (!inputValue) return []; // Don't search if input is empty

        try {
            const response = await fetch(
                `/wp-json/wc/v3/products?search=${encodeURIComponent(inputValue)}`,
                {
                    headers: {
                        'X-WP-Nonce': wpApiSettings.nonce, // Use the nonce for authentication
                    },
                }
            );

            if (!response.ok) {
                throw new Error('Failed to fetch products');
            }

            const products = await response.json();
            return products.map((product) => ({
                value: product.id, // Use product ID as the value
                label: product.name, // Use product name as the label
            }));
        } catch (error) {
            console.error('Error fetching products:', error);
            return [];
        }
    };
    const handleRemoveProduct = (productId) => {
        setSelectedProducts((prevSelectedProducts) =>
            prevSelectedProducts.filter((product) => product.value !== productId)
        );
    };

    const handleSelectChange = (selectedOptions) => {
        setSelectedProducts(prevSelected => {
            // Ensure previous selected items are spread properly
            const newSelections = Array.isArray(selectedOptions) ? selectedOptions : [selectedOptions];

            // Prevent duplicates by checking if value already exists
            const uniqueProducts = [...prevSelected, ...newSelections].reduce((acc, product) => {
                if (!acc.some(item => item.value === product.value)) {
                    acc.push(product);
                }
                return acc;
            }, []);

            return uniqueProducts;
        });
    };
    const customComponents = {
        MultiValueRemove: () => null, // Disables the delete (✖) icon
    };

    return (
        <div className="container form-wrapper">
            <div className="header-section">
                <h2>{id ? "Edit Notification" : "Add New Notification"}</h2>
                <button className="add-btn" onClick={() => navigate("/")}>Go Back</button>
            </div>
            <div className="form-container">

                <form onSubmit={handleSubmit}>
                    <div className="section-title"><h2>Basic</h2></div>
                    <div class="row">
                        <div className="col-25">
                            <label for="fname">Title</label>
                        </div>
                        <div className="col-75">
                            <input
                                type="text"
                                value={title}
                                onChange={(e) => setTitle(e.target.value)}
                                required
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div className="col-25">
                            <label for="fname"> Enable Notification</label>
                        </div>
                        <div className="col-75">
                            <input
                                type="checkbox"
                                checked={isEnabled}
                                onChange={(e) => setIsEnabled(e.target.checked)}
                            />
                            Enable/Disable
                        </div>
                    </div>
                    <div class="row">
                        <div className="col-25">
                            <label for="fname">Notification Type</label>
                        </div>
                        <div className="col-75">
                            <select name="type" value={notificationType} onChange={(e) => setNotificationType(e.target.value)}>
                                <option value="message">Message</option>
                                <option value="push-notifications">Push Notifications</option>
                                <option value="alert">Alert (Display within product)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div className="col-25">
                            <label for="fname">Notification Content</label>
                        </div>
                        <div className="col-75">
                            <textarea
                                value={content}
                                onChange={(e) => setContent(e.target.value)}
                                required
                            ></textarea>

                        </div>
                    </div>
                    <div className="section-title"><h2>What to Track</h2></div>
                    <div class="row">
                        <div className="col-25">
                            <label for="fname">Track only Specific Category</label>
                        </div>
                        <div className="col-75">
                            <AsyncSelect
                                classNamePrefix="pricedropnotifexpert-select"
                                isMulti
                                cacheOptions
                                defaultOptions
                                loadOptions={fetchProductCategories}
                                onChange={(selectedOptions) => setSelectedCategories(selectedOptions)}
                                placeholder="Select product categories..."
                                value={selectedCategories}
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div className="col-25">
                            <label for="fname">Track only Specific Products</label>
                        </div>
                        <div className="col-75">
                            <AsyncSelect
                                classNamePrefix="pricedropnotifexpert-select"
                                isMulti
                                cacheOptions
                                defaultOptions
                                loadOptions={loadOptions}
                                onChange={(selectedOptions) => handleSelectChange(selectedOptions)}
                                placeholder="Search and select products..."
                                closeMenuOnSelect={false}
                                value={selectedProducts}
                                components={customComponents}
                            />
                            {selectedProducts.length > 0 && (
                                <div className="selected-products-list">
                                    <ul>
                                        {selectedProducts.map((product, index) => (
                                            <li key={product.value}> {/* Or key={index} if product.value might be duplicated */}
                                                <span>{product.label} (ID: {product.value})</span>
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setSelectedProducts(
                                                            selectedProducts.filter((p) => p.value !== product.value)
                                                        );
                                                    }}
                                                    className="remove-product-btn"
                                                >
                                                    &times;
                                                </button>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                        </div>
                    </div>
                    <div className="section-title"><h2>How to Track</h2></div>
                    <div class="row">
                        <div className="col-25">
                            <label for="fname">Tracking Type</label>
                        </div>
                        <div className="col-75">
                            <select name="tracking_type" value={trackingType} onChange={(e) => setTrackingType(e.target.value)}>
                                <option value="all">All Changes (Starting Now)</option>
                                <option value="from-specific-date">From Specific Date</option>
                                <option value="at-specific-date">At Specific Date</option>
                                <option value="between-dates">Between Specific Dates</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div className="col-25">
                            <label for="start_date">{trackingType && trackingType == 'between-dates' ? 'Date Range' : 'Date'}</label>
                        </div>
                        <div className="col-75">
                            <input
                                type="date"
                                name="start_date"
                                value={startDate}
                                onChange={(e) => setStartDate(e.target.value)}
                                required
                            />
                            {trackingType && trackingType == 'between-dates' &&
                                <input
                                    type="date"
                                    name="end_date"
                                    value={endDate}
                                    onChange={(e) => setEndDate(e.target.value)}
                                />
                            }
                        </div>


                    </div>

                    <div className="button-wrapper">
                        {/* Submit and Cancel Buttons */}
                        <button type="submit" className="button button-primary">{id ? " Update" : "Create"}</button>
                        <button type="button" className="button cancel-btn" onClick={() => navigate("/")}>Cancel</button>
                    </div>
                </form>
                {
                    notifMessage && (
                        <span className={`error-success-notif-message ${notifType}`}>
                            {notifMessage}
                        </span>
                    )
                }
            </div >
        </div >
    );
};

export default NotificationForm;
