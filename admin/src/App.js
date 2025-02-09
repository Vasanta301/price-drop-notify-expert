import React from "react";
import { HashRouter as Router, Routes, Route, Navigate } from "react-router-dom";
import PriceDropTable from "./PriceDropTable";
import NotificationForm from "./NotificationForm";

const App = () => {
    return (
        <Router>
            <Routes>
                <Route path="/" element={<PriceDropTable />} />
                <Route path="/add" element={<NotificationForm />} />
                <Route path="/edit/:id" element={<NotificationForm />} />
                {/* Catch all unmatched routes and load PriceDropTable */}
                <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
        </Router>
    );
};

export default App;
