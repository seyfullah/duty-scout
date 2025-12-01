using System;
using System.Collections.Generic;

public class Group
{
    public Guid Id { get; set; } = Guid.NewGuid();
    public string Name { get; set; } = string.Empty;
    public string Responsible { get; set; } = string.Empty;
    public List<Member> Members { get; set; } = new();
    public List<Group> Subgroups { get; set; } = new();
}

public class Member
{
    public Guid Id { get; set; } = Guid.NewGuid();
    public string Name { get; set; } = string.Empty;
}