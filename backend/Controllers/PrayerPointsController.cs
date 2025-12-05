using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;

[ApiController]
[Route("api/[controller]")]
public class PrayerPointsController : ControllerBase
{
    private static readonly List<PrayerPoint> _prayerPoints = new();

    public class CreatePrayerPointRequest
    {
        public Guid MemberId { get; set; }
        public string MemberName { get; set; } = string.Empty;
        public Guid GroupId { get; set; }
        public string PrayerType { get; set; } = string.Empty;
        public bool IsAtMosque { get; set; }
        public int Points { get; set; }
        public DateTime Date { get; set; }
        public int HijriYear { get; set; }
        public int HijriMonth { get; set; }
    }

    [HttpGet]
    public ActionResult<IEnumerable<PrayerPoint>> Get() => Ok(_prayerPoints);

    [HttpPost]
    public ActionResult<PrayerPoint> Create([FromBody] CreatePrayerPointRequest req)
    {
        var pp = new PrayerPoint
        {
            MemberId = req.MemberId,
            MemberName = req.MemberName,
            GroupId = req.GroupId,
            PrayerType = req.PrayerType,
            IsAtMosque = req.IsAtMosque,
            Points = req.Points,
            Date = req.Date,
            HijriYear = req.HijriYear,
            HijriMonth = req.HijriMonth,
        };
        _prayerPoints.Add(pp);
        return CreatedAtAction(nameof(Get), pp);
    }

    [HttpDelete("{id:guid}")]
    public IActionResult Delete(Guid id)
    {
        var pp = _prayerPoints.FirstOrDefault(x => x.Id == id);
        if (pp == null) return NotFound();
        _prayerPoints.Remove(pp);
        return NoContent();
    }
}