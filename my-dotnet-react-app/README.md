# My .NET and React Application

This project is a full-stack application that combines a .NET backend with a React frontend. The backend serves weather forecast data, while the frontend displays this data to the user.

## Project Structure

```
my-dotnet-react-app
├── backend
│   ├── Controllers
│   │   └── WeatherForecastController.cs
│   ├── Models
│   │   └── WeatherForecast.cs
│   ├── Program.cs
│   ├── Startup.cs
│   └── my-dotnet-react-app.csproj
├── frontend
│   ├── public
│   │   └── index.html
│   ├── src
│   │   ├── App.tsx
│   │   ├── index.tsx
│   │   └── components
│   │       └── Weather.tsx
│   ├── package.json
│   └── tsconfig.json
└── README.md
```

## Getting Started

### Prerequisites

- .NET 10.0 or later
- Node.js and npm

### Backend Setup

1. Navigate to the `backend` directory.
2. Restore the dependencies:
   ```
   dotnet restore
   ```
3. Run the application:
   ```
   dotnet run
   ```

The backend will be running on `http://localhost:5000` by default.

### Frontend Setup

1. Navigate to the `frontend` directory.
2. Install the dependencies:
   ```
   npm install
   ```
3. Start the React application:
   ```
   npm start
   ```

The frontend will be running on `http://localhost:3000` by default.

### Communication

The React application fetches weather data from the .NET backend using HTTP GET requests to the `/weatherforecast` endpoint.

## Features

- Fetch and display weather forecast data.
- Responsive and user-friendly interface.

## License

This project is licensed under the MIT License.