using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;

[ApiController]
[Route("api/[controller]")]
public class GroupsController : ControllerBase
{
    private static readonly List<Group> _groups = new();

    public class CreateGroupRequest 
    { 
        public string Name { get; set; } = string.Empty;
        public string Responsible { get; set; } = string.Empty;
    }

    public class AddMemberRequest
    {
        public string Name { get; set; } = string.Empty;
    }

    [HttpGet]
    public ActionResult<IEnumerable<Group>> Get() => Ok(_groups);

    [HttpGet("{id:guid}")]
    public ActionResult<Group> GetById(Guid id)
    {
        var g = _groups.FirstOrDefault(x => x.Id == id);
        if (g == null) return NotFound();
        return Ok(g);
    }

    // Grup Oluştur
    [HttpPost]
    public ActionResult<Group> CreateGroup([FromBody] CreateGroupRequest req)
    {
        if (string.IsNullOrWhiteSpace(req?.Name)) return BadRequest("Name required");
        var g = new Group 
        { 
            Name = req.Name,
            Responsible = req.Responsible ?? string.Empty
        };
        _groups.Add(g);
        return CreatedAtAction(nameof(GetById), new { id = g.Id }, g);
    }

    // Üye Ekle
    [HttpPost("{groupId:guid}/members")]
    public ActionResult<Member> AddMember(Guid groupId, [FromBody] AddMemberRequest req)
    {
        var group = _groups.FirstOrDefault(x => x.Id == groupId);
        if (group == null) return NotFound("Group not found");
        if (string.IsNullOrWhiteSpace(req?.Name)) return BadRequest("Member name required");

        var member = new Member { Name = req.Name };
        group.Members.Add(member);
        return CreatedAtAction(nameof(GetById), new { id = groupId }, member);
    }

    // Üye Sil
    [HttpDelete("{groupId:guid}/members/{memberId:guid}")]
    public IActionResult DeleteMember(Guid groupId, Guid memberId)
    {
        var group = _groups.FirstOrDefault(x => x.Id == groupId);
        if (group == null) return NotFound("Group not found");
        var member = group.Members.FirstOrDefault(m => m.Id == memberId);
        if (member == null) return NotFound("Member not found");
        group.Members.Remove(member);
        return NoContent();
    }

    // Grubu Güncelle
    [HttpPut("{id:guid}")]
    public ActionResult<Group> UpdateGroup(Guid id, [FromBody] CreateGroupRequest req)
    {
        var g = _groups.FirstOrDefault(x => x.Id == id);
        if (g == null) return NotFound();
        if (string.IsNullOrWhiteSpace(req?.Name)) return BadRequest("Name required");
        g.Name = req.Name;
        g.Responsible = req.Responsible ?? string.Empty;
        return Ok(g);
    }

    // Grubu Sil (üyeler de silinir)
    [HttpDelete("{id:guid}")]
    public IActionResult DeleteGroup(Guid id)
    {
        var g = _groups.FirstOrDefault(x => x.Id == id);
        if (g == null) return NotFound();
        _groups.Remove(g);
        return NoContent();
    }

    // Alt Grup Oluştur
    [HttpPost("{id:guid}/subgroups")]
    public ActionResult<Group> CreateSubgroup(Guid id, [FromBody] CreateGroupRequest req)
    {
        var parent = _groups.FirstOrDefault(x => x.Id == id);
        if (parent == null) return NotFound();
        if (string.IsNullOrWhiteSpace(req?.Name)) return BadRequest("Name required");
        var sub = new Group 
        { 
            Name = req.Name,
            Responsible = req.Responsible ?? string.Empty
        };
        parent.Subgroups.Add(sub);
        return CreatedAtAction(nameof(GetById), new { id = parent.Id }, sub);
    }

    // Alt Grup Sil
    [HttpDelete("{groupId:guid}/subgroups/{subId:guid}")]
    public IActionResult DeleteSubgroup(Guid groupId, Guid subId)
    {
        var parent = _groups.FirstOrDefault(x => x.Id == groupId);
        if (parent == null) return NotFound();
        var sub = parent.Subgroups.FirstOrDefault(s => s.Id == subId);
        if (sub == null) return NotFound();
        parent.Subgroups.Remove(sub);
        return NoContent();
    }
}