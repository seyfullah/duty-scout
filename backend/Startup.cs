using Microsoft.AspNetCore.Builder;
using Microsoft.Extensions.DependencyInjection;

public class Startup
{
    public void ConfigureServices(IServiceCollection services)
    {
        services.AddControllers();
        services.AddCors(options =>
        {
            options.AddPolicy("AllowAll",
                builder => builder.AllowAnyOrigin().AllowAnyMethod().AllowAnyHeader());
        });

        // Swagger
        services.AddEndpointsApiExplorer();
        services.AddSwaggerGen(c =>
        {
            c.SwaggerDoc("v1", new Microsoft.OpenApi.Models.OpenApiInfo
            { 
                Title = "Duty Scout API", 
                Version = "v1",
                Description = "API for managing groups and subgroups"
            });
        });
    }

    public void Configure(WebApplication app)
    {
        // Swagger her zaman etkin
        app.UseSwagger();
        app.UseSwaggerUI(c =>
        {
            c.SwaggerEndpoint("/swagger/v1/swagger.json", "Duty Scout API v1");
            c.RoutePrefix = "swagger"; // http://localhost:5000/swagger
        });

        app.UseDeveloperExceptionPage();
        app.UseHttpsRedirection();
        app.UseRouting();
        app.UseAuthentication();
        app.UseCors("AllowAll");
        app.UseAuthorization();

        app.MapControllers();
    }
}