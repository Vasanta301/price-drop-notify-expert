import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

const NotFound = () => {
    return (
        <div className="price-drop-container">
            <div className="header-section">
                <h2>Page Not Found</h2>
                <button className="add-btn" onClick={() => navigate("/")}>Back To Homepage</button>
            </div>
        </div>
    );
};

export default NotFound;
