using System;

public class PrayerPoint
{
    public Guid Id { get; set; } = Guid.NewGuid();
    public Guid MemberId { get; set; }
    public string MemberName { get; set; } = string.Empty;
    public Guid GroupId { get; set; }
    public string PrayerType { get; set; } = string.Empty; // fajr, dhuhr, asr, maghrib, isha
    public bool IsAtMosque { get; set; }
    public int Points { get; set; }
    public DateTime Date { get; set; }
    public int HijriYear { get; set; }
    public int HijriMonth { get; set; }
}