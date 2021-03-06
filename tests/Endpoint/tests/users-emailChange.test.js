const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
let jwt = require("jsonwebtoken");
const { getEmail } = require("../emailHelper.js");

/**
 * Tests for the /users/{x}/email-change route
 */
makeSuite(["3roles", "2Users"], "/users/{x}/email-change", {
  PUT: notAllowed(),
  DELETE: notAllowed(),
  PATCH: notAllowed(),
  GET: notAllowed(),
  POST: {
    "without accessToken": () => {
      it("makes api call", async () => {
        this.response = await request.post("/users/1/email-change");
      });

      it("returns Unauthorized", async () => {
        expect(this.response.statusCode).to.eql(401);
      });
    },

    "without permission": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 2,
            perm: "changeOwnEmail",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/email-change")
          .set("Authorization", "Bearer " + token);
      });

      it("returns Forbidden", async () => {
        expect(this.response.statusCode).to.eql(403);
      });
    },
    "without an email": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeOwnEmail",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/email-change")
          .set("Authorization", "Bearer " + token);
      });
      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["errorCode"]).to.eql(101);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("require");
      });

      it("includes a list of required properties", async () => {
        expect(this.response.body["missingProperties"]).to.has.keys(["email"]);
      });
    },
    "with invalid email": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeOwnEmail",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/email-change")
          .set("Authorization", "Bearer " + token)
          .send({ email: "Password111" });
      });
      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["errorCode"]).to.eql(102);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("invalid");
      });

      it("includes a list of invalid properties", async () => {
        expect(this.response.body["invalidProperties"]["email"][0]).to.eq(
          "NO_EMAIL"
        );
      });
    },
    "with taken email": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeOwnEmail",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/email-change")
          .set("Authorization", "Bearer " + token)
          .send({ email: "user2@mail.de" });
      });
      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["errorCode"]).to.eql(102);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("invalid");
      });

      it("includes a list of invalid properties", async () => {
        expect(this.response.body["invalidProperties"]["email"][0]).to.eq(
          "IS_TAKEN"
        );
      });
    },
    successful: () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeOwnEmail",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/email-change")
          .set("Authorization", "Bearer " + token)
          .send({ email: process.env.TEST_MAIL_RECEIVER });
      }).timeout(30000);

      it("returns Created", async () => {
        expect(this.response.statusCode).to.eql(201);
      });

      it("includes no body", async () => {
        expect(this.response.body).to.be.empty;
      });

      it("sends an email", async () => {
        //sleep a minute
        await new Promise((r) => setTimeout(r, 20000));
        //get the newest unread email
        this.email = await getEmail();
        //expect getEmail finds an email
        expect(this.email).not.be.false;
        //expect mail not older than 60 secs
        expect(new Date(this.email.date).getTime()).to.be.closeTo(
          new Date().getTime(),
          60000
        );
      }).timeout(30000);

      it("sets 'to:' correctly", async () => {
        expect(this.email.to.text).to.eql(
          `user1 <${process.env.TEST_MAIL_RECEIVER}>`
        );
      });
      it("sets 'from:' correctly", async () => {
        expect(this.email.from.text).to.eql(
          `SkyGateCaseStudy <no-reply@test.de>`
        );
      });

      it("sets 'subject' correctly", async () => {
        expect(this.email.subject).to.eql(`Verify your new Email!`);
      });

      it("includes the link in plain text", async () => {
        splitStr = this.email.text.split("link: ");
        expect(splitStr[0]).to.eql(
          `Please verify your new email by following this `
        );
        this.plainLink = splitStr[1].trim();
      });
      it("The link is correct", async () => {
        splitLink = this.plainLink.split("/");
        //domain
        expect(splitLink[0]).to.eql(`${process.env.APP_PROD_DOMAIN}`);
        splitLink = splitLink[1].split("?");
        //path
        expect(splitLink[0]).to.eql("change-email");
        splitLink = splitLink[1].split("&");
        //query parameter 1
        expect(splitLink[0]).to.eql("userID=1");
        //query parameter 2
        splitLink = splitLink[1].split("=");
        expect(splitLink[0]).to.eql("code");
        expect(splitLink[1]).to.match(/^[0-9a-f]{10}$/);
      });
    },
  },
});
