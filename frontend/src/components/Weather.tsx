import React, { useEffect, useState } from 'react';

const Weather: React.FC = () => {
    const [weatherData, setWeatherData] = useState<any[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchWeatherData = async () => {
            try {
                const res = await fetch('http://localhost:5000/weatherforecast');
                const data = await res.json();
                setWeatherData(data);
            } catch (error: unknown) {
                if (error instanceof Error) {
                    setError(error.message);
                } else {
                    setError(String(error));
                }
            } finally {
                setLoading(false);
            }
        };

        fetchWeatherData();
    }, []);

    if (loading) {
        return <div>Loading...</div>;
    }

    if (error) {
        return <div>Error: {error}</div>;
    }

    return (
        <div>
            <h2>Weather Forecast</h2>
            <ul>
                {weatherData.map((forecast) => (
                    <li key={forecast.date}>
                        {forecast.date}: {forecast.temperatureC}°C - {forecast.summary}
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default Weather;