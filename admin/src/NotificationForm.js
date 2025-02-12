import React, { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import AsyncSelect from 'react-select/async';
import { applyFilters, doAction } from '@wordpress/hooks';
import { Dashicon } from '@wordpress/components';
const NotificationForm = () => {
    const notificationTypeOptions = [
        { value: 'info-graph', label: 'InfoGraphs (Display within product)' },
        { value: 'email', label: 'Email' },
        { value: 'banner', label: 'Sales Notification Banner' },
        { value: 'push-notifications', label: 'Push Notifications' },
        { value: 'sms', label: 'SMS Message' },
    ];

    const { id } = useParams();
    const navigate = useNavigate();
    const [notifMessage, setNotifMessage] = useState(""); // State to store the message
    const [notifType, setNotifType] = useState(""); // "error" or "success"
    const [title, setTitle] = useState("");
    const [content, setContent] = useState("");
    const [trackingType, setTrackingType] = useState("");
    const [startDate, setStartDate] = useState("");
    const [endDate, setEndDate] = useState("");
    const [notificationType, setNotificationType] = useState("info-graph"); // Default type
    const [isEnabled, setIsEnabled] = useState('false'); // Default enabled
    const [selectedProducts, setSelectedProducts] = useState([]);
    const [selectedCategories, setSelectedCategories] = useState([]);
    const [notificationNature, setNotificationNature] = useState("individual");
    const [trackProductsBy, setTrackProductsBy] = useState("all");
    const [priceHistoryTitle, setPriceHistoryTitle] = useState("");
    const [isEnabledInfoGraph, setIsEnabledInfoGraph] = useState('false');
    const [emaillHeader, setEmaillHeader] = useState("");
    //Set Base URL
    const base_url = `/wp-json/pricedropnotifexpert/v1`;
    useEffect(() => {
        if (id) {
            // Fetch notification data when editing
            fetch(`${base_url}/notification/${id}`)
                .then((res) => res.json())
                .then((data) => {
                    if (Array.isArray(data) && data.length > 0) {
                        console.log(data[0].enabled);
                        setIsEnabled(data[0].enabled === 'true' ? 'true' : 'false');
                        setTitle(data[0].title || "");
                        setContent(data[0].content || "");
                        setStartDate(data[0].start_date ? new Date(data[0].start_date).toISOString().split('T')[0] : "");
                        setEndDate(data[0].end_date ? new Date(data[0].end_date).toISOString().split('T')[0] : "");
                        setNotificationType(data[0].notification_type || ""); //Notification Type : info-graph, email, message...
                        setTrackingType(data[0].tracking_type || "all"); //Track product | date starting now, date after x, between dates...
                        setNotificationNature(data[0].notification_nature || "individual");
                        setTrackProductsBy(data[0].track_products_by || "all"); //Track product by all, products selected, category selected...
                        setSelectedProducts(data[0].selected_products || []); //Track product by products selected
                        setSelectedCategories(data[0].selected_categories || []); //Track product by category selected...
                        setPriceHistoryTitle(data[0].price_history_title || "");
                        setEmailHeader(data[0].email_header || "");
                        setIsEnabledInfoGraph(data[0].enabled_info_graph === 'true' ? 'true' : 'false');
                    }
                })
                .catch((err) => console.error("Error fetching notification:", err));
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
                    'X-WP-Nonce': wpApiSettings.nonce, // Use the nonce for authentication
                },
                body: JSON.stringify({
                    title,
                    content,
                    tracking_type: trackingType,
                    start_date: startDate,
                    end_date: endDate,
                    notification_type: notificationType,
                    enabled: isEnabled,
                    selected_categories: selectedCategories,
                    selected_products: selectedProducts,
                    notification_nature: notificationNature,
                    track_products_by: trackProductsBy,
                    price_history_title: priceHistoryTitle,
                    graphEnabled: isEnabledInfoGraph,
                    status: "publish",
                }),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || "Failed to save");
            } else {
                const responseData = await response.json();
                if (responseData.message) {
                    setNotifMessage(responseData.message);
                    setNotifType(responseData.success ? "success" : "error");
                } else {
                    setNotifMessage('Default Message');
                    setNotifType(responseData.success ? "success" : "error");
                }
                setTimeout(() => {
                    setNotifMessage(""); // Clear after 4 seconds
                }, 4000);

                // Redirect to the correct route
                if (id) {
                    navigate(`/edit/${id}`);
                } else {
                    // After creation, redirect to the edit page of the new notification
                    navigate(`/edit/${responseData.id}`);
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
    useEffect(() => {
        //console.log('isEnabled changed:', isEnabled);
    }, [isEnabled]);
    return (
        <div className="container form-wrapper">
            <div className="header-section">
                <h2>{id ? "Edit Notification" : "Add New Notification"}</h2>
                <button className="pdne-primary-btn btn-go-back" onClick={() => navigate("/")}><Dashicon icon="arrow-left-alt" /> Go Back</button>
            </div>
            <div className="form-container">

                <form onSubmit={handleSubmit}>
                    <section>
                        <div className="row">
                            <div className="col-25">
                                <label htmlFor="enableNotification">Enable Notification</label>
                            </div>
                            <div className="col-75">

                                <input
                                    type="checkbox"
                                    id="enableNotification"
                                    checked={isEnabled == 'true'}
                                    onChange={(e) => setIsEnabled(e.target.checked ? 'true' : 'false')}
                                />
                                <span htmlFor="enableNotification"> Enable/Disable</span>
                            </div>
                        </div>
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
                    </section>
                    <section>
                        <div className="section-title"><h2>What to Send</h2></div>

                        <div class="row">
                            <div className="col-25">
                                <label for="fname">Notification Type</label>
                            </div>
                            <div className="col-75">
                                <select name="type" value={notificationType} onChange={(e) => setNotificationType(e.target.value)}>
                                    {notificationTypeOptions.map((option, index) => (
                                        <option key={index} value={option.value}>
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </section>
                    {notificationType && notificationType === 'info-graph' && (
                        <>

                            <section>
                                <div className="section-title"><h2> Infograph Configuration</h2><hr /></div>
                                {/* Enable Graph */}
                                <div className="row">
                                    <div className="col-25">
                                        <label htmlFor="enableGraph">Enable Graph</label>
                                    </div>
                                    <div className="col-75">
                                        <input
                                            type="checkbox"
                                            id="enableGraph"
                                            checked={isEnabledInfoGraph == 'true'}
                                            onChange={(e) => setIsEnabledInfoGraph(e.target.checked ? 'true' : 'false')}
                                        />
                                        <span> Enable/Disable</span>
                                    </div>
                                </div>

                                {/* Notification Content */}
                                <div className="row">
                                    <div className="col-25">
                                        <label htmlFor="notificationContent">Notification Content</label>
                                    </div>
                                    <div className="col-75">
                                        <textarea
                                            id="notificationContent"
                                            value={content}
                                            onChange={(e) => setContent(e.target.value)}
                                            required
                                        ></textarea>
                                    </div>
                                </div>

                                {/* What to Track */}
                                <div className="section-title">
                                    <h2>What to Track</h2>
                                </div>
                                <div className="row">
                                    <div className="col-25">
                                        <label htmlFor="trackProductsBy">Track Products By</label>
                                    </div>
                                    <div className="col-75">
                                        <select
                                            name="type"
                                            id="trackProductsBy"
                                            value={trackProductsBy}
                                            onChange={(e) => setTrackProductsBy(e.target.value)}
                                        >
                                            <option value="all">All Products</option>
                                            <option value="categories">Categories</option>
                                            <option value="products">Products</option>
                                        </select>
                                    </div>
                                </div>

                                {/* Product Category Selector (if tracking by categories) */}
                                {trackProductsBy === 'categories' && (
                                    <div className="row">
                                        <div className="col-25">
                                            <label htmlFor="productCategory">Product Category</label>
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
                                )}

                                {/* Specific Products Selector (if tracking by products) */}
                                {trackProductsBy === 'products' && (
                                    <div className="row">
                                        <div className="col-25">
                                            <label htmlFor="specificProducts">Track only Specific Products</label>
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
                                                        {selectedProducts.map((product) => (
                                                            <li key={product.value}>
                                                                <span>{product.label} (ID: {product.value})</span>
                                                                <button
                                                                    type="button"
                                                                    onClick={() =>
                                                                        setSelectedProducts(
                                                                            selectedProducts.filter((p) => p.value !== product.value)
                                                                        )
                                                                    }
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
                                )}

                                {/* How to Track */}
                                <div className="section-title">
                                    <h2>How to Track</h2>
                                </div>
                                <div className="row">
                                    <div className="col-25">
                                        <label htmlFor="trackingType">Tracking Type</label>
                                    </div>
                                    <div className="col-75">
                                        <select
                                            name="tracking_type"
                                            id="trackingType"
                                            value={trackingType}
                                            onChange={(e) => setTrackingType(e.target.value)}
                                        >
                                            <option value="all">All Changes (Starting Now)</option>
                                            <option value="from-specific-date">From Specific Date</option>
                                            <option value="at-specific-date">At Specific Date</option>
                                            <option value="between-dates">Between Specific Dates</option>
                                        </select>
                                    </div>
                                </div>

                                {/* Date Selector */}
                                <div className="row">
                                    <div className="col-25">
                                        <label htmlFor="start_date">
                                            {trackingType === 'between-dates' ? 'Date Range' : 'Date'}
                                        </label>
                                    </div>
                                    <div className="col-75">
                                        <input
                                            type="date"
                                            name="start_date"
                                            id="start_date"
                                            value={startDate}
                                            onChange={(e) => setStartDate(e.target.value)}
                                            required
                                        />
                                        {trackingType === 'between-dates' && (
                                            <input
                                                type="date"
                                                name="end_date"
                                                id="end_date"
                                                value={endDate}
                                                onChange={(e) => setEndDate(e.target.value)}
                                            />
                                        )}
                                    </div>
                                </div>
                            </section>
                        </>
                    )}
                    {notificationType && notificationType === 'email' && (
                        <>

                            <section>
                                <div className="section-title"><h2>Email Configuration</h2><hr /></div>
                                {/* Enable Graph */}
                                <div className="row">
                                    <div className="col-25">
                                        <label htmlFor="EMail Header">Email Subject</label>
                                    </div>
                                    <div className="col-75">
                                        <input
                                            type="text"
                                            id="enableGraph"
                                            value={emaillHeader}
                                            onChange={(e) => setEmaillHeader(e.target.value)}
                                        />
                                    </div>
                                </div>
                            </section>
                        </>
                    )}
                    <section>
                        <div className="button-wrapper">
                            {/* Submit and Cancel Buttons */}
                            <button type="submit" className="pdne-primary-btn save-add-btn">
                                <Dashicon icon={id ? "cloud-saved" : "insert"} /> <span>{id ? " Update" : "Create"}</span>
                            </button>
                            <button type="button" className="pdne-primary-btn cancel-btn" onClick={() => navigate("/")}>Cancel</button>
                        </div>
                    </section>
                </form>
                {
                    notifMessage && (
                        <>
                            <span className={`error-success-notif-message ${notifType}`}>
                                {notifMessage}
                            </span>
                            <span>
                                {notifMessage}
                            </span>
                        </>
                    )
                }
            </div >
        </div >
    );
};

export default NotificationForm;
