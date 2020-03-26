## API Auth
- Every request has to have Authorization HTTP Headera with JWT
- Only exception is /login
    -   /login checks credentials and returns JWT
___
## API Endpoints

## 1st wave
### /proposal-votes
```
/proposal-votes [POST]
    - POST data with proposal votes, accepts json body (
        "member_ID": int,
        "votes": {
          {
            "proposal_ID": int,
            "value": int
          },
          {
            "proposal_ID": int,
            "value": int
          },
          .
          .
          .
        }
      )
```
___
### /candidate-votes
```
/candidate-votes [POST]
    - POST data with proposal votes, accepts json body (
        "member_ID": int,
        "votes": {
          {
            "candidate_ID": int,
            "value": int
          },
          {
            "candidate_ID": int,
            "value": int
          },
          .
          .
          .
        }
      )
```
___
### /sabats
```
/sabats				[GET]
    - GET list of all upcoming sabats

/sabats/{ID}/proposals  [GET, POST]
    - GET list of all proposals for sabat with {ID}
    - POST new proposal to database, accepts json body (
        "member_ID": int,
        "name": "string",
        "description": "string"
      )

/sabats/{ID}/candidates [GET, POST]
    - GET list of all candidates for sabat with {ID}
    - POST new candidate to database, accepts json body (
        "member_ID": int,
        "role_ID": int
      )
```
___
### /login
```
/login          [POST]
    - checks if member number is valid, accepts json body (
        "member_number": int,
      )
    - returns JWT
```
___

## 2nd wave

### /regional-cells
```
/regional-cells [GET]
    - GET list of all regional cells

/regional-cells/{ID}/sabats [GET]
    - GET list of all upcoming sabats for regional cell with {ID}

```
___
