using Microsoft.AspNetCore.Builder;
using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.DependencyInjection;

public class Program
{
    public static void Main(string[] args)
    {
        var builder = WebApplication.CreateBuilder(args);

        var startup = new Startup();
        startup.ConfigureServices(builder.Services);

        var app = builder.Build();

        startup.Configure(app);

        app.Run();
    }
}